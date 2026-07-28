/**
 * Sample Vercel Serverless Function for Bank of England Gilt Yields
 *
 * This function fetches gilt yield data from Bank of England CSV
 * and exposes it as a JSON endpoint for the UK Yield Rates WordPress plugin.
 *
 * Deploy to Vercel (free tier: 100,000 requests/month)
 *
 * Setup:
 * 1. Go to https://vercel.com/
 * 2. Create a new project
 * 3. Create a file: api/yields.js (this file)
 * 4. Deploy
 * 5. Your endpoint will be: https://your-project.vercel.app/api/yields
 * 6. Copy the URL and paste it in the plugin settings
 *
 * Expected output format:
 * {
 *   "yields": {
 *     "2": {"yield": 4.25, "change": 0.02, "date": "2026-07-28"},
 *     "5": {"yield": 4.15, "change": -0.01, "date": "2026-07-28"},
 *     "10": {"yield": 4.05, "change": 0.00, "date": "2026-07-28"},
 *     "20": {"yield": 4.35, "change": 0.03, "date": "2026-07-28"},
 *     "30": {"yield": 4.45, "change": -0.02, "date": "2026-07-28"}
 *   }
 * }
 */

export default async function handler(req, res) {
  // Bank of England yield curve data URL
  // Series codes: IUDM421 (2Y), IUDM423 (5Y), IUDM425 (10Y), IUDM427 (20Y), IUDM429 (30Y)
  const csvUrl = 'https://www.bankofengland.co.uk/boeapps/database/_iad-downloadseries.asp?SeriesCodes=IUDM421,IUDM423,IUDM425,IUDM427,IUDM429&CSVF=TN&UsingCodes=Y&Period=Daily';

  try {
    // Fetch CSV data from Bank of England
    const response = await fetch(csvUrl, {
      headers: {
        'User-Agent': 'UK-Yield-Rates-Plugin/1.0',
      },
    });

    if (!response.ok) {
      throw new Error(`BoE request failed: ${response.status}`);
    }

    const csvText = await response.text();
    const yields = parseBoECSV(csvText);

    // Set CORS and caching headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
    res.setHeader('Content-Type', 'application/json');

    return res.status(200).json({ yields });
  } catch (error) {
    console.error('Error fetching BoE data:', error);
    return res.status(500).json({
      error: 'Failed to fetch yield data',
      message: error.message
    });
  }
}

/**
 * Parse Bank of England CSV data and extract yields
 */
function parseBoECSV(csvText) {
  const lines = csvText.split('\n');
  const yields = {};

  // Maturity mapping (series code to maturity)
  const maturityMap = {
    'IUDM421': '2',
    'IUDM423': '5',
    'IUDM425': '10',
    'IUDM427': '20',
    'IUDM429': '30',
  };

  // Find header row and identify column positions
  let headerIndex = -1;
  let dateIndex = -1;
  const seriesColumns = {};

  for (let i = 0; i < Math.min(lines.length, 50); i++) {
    const line = lines[i];
    if (line.includes('DATE') || line.includes('RVAC')) {
      headerIndex = i;
      const columns = line.split(',').map(col => col.trim().replace(/"/g, ''));

      columns.forEach((col, index) => {
        if (col === 'DATE' || col === 'RVAC') {
          dateIndex = index;
        }
        if (maturityMap[col]) {
          seriesColumns[maturityMap[col]] = index;
        }
      });
      break;
    }
  }

  if (headerIndex === -1 || Object.keys(seriesColumns).length === 0) {
    throw new Error('Could not parse CSV header');
  }

  // Find the last data row with valid data
  let lastDataRow = null;
  let previousDataRow = null;

  for (let i = lines.length - 1; i > headerIndex; i--) {
    const line = lines[i].trim();
    if (!line || line.startsWith('#') || line.startsWith('DATE')) continue;

    const columns = line.split(',').map(col => col.trim().replace(/"/g, ''));

    if (columns[dateIndex] && columns[Object.values(seriesColumns)[0]]) {
      if (lastDataRow === null) {
        lastDataRow = columns;
      } else if (previousDataRow === null) {
        previousDataRow = columns;
        break;
      }
    }
  }

  if (!lastDataRow) {
    throw new Error('No valid data rows found');
  }

  // Extract yields for each maturity
  for (const [maturity, colIndex] of Object.entries(seriesColumns)) {
    const currentValue = parseFloat(lastDataRow[colIndex]);
    const previousValue = previousDataRow ? parseFloat(previousDataRow[colIndex]) : currentValue;

    if (!isNaN(currentValue)) {
      yields[maturity] = {
        yield: currentValue,
        change: isNaN(previousValue) ? 0 : Math.round((currentValue - previousValue) * 100) / 100,
        date: lastDataRow[dateIndex] || new Date().toISOString().split('T')[0],
      };
    }
  }

  return yields;
}
