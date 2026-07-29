/**
 * Vercel Serverless Function for Bank of England Gilt Yields
 *
 * This function fetches gilt yield data from Bank of England ZIP archive
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
  // Bank of England yield curve data URL (ZIP archive)
  const zipUrl = 'https://www.bankofengland.co.uk/-/media/boe/files/statistics/yield-curves/latest-yield-curve-data.zip';

  try {
    // Fetch ZIP data from Bank of England
    const response = await fetch(zipUrl, {
      headers: {
        'User-Agent': 'UK-Yield-Rates-Plugin/1.0',
      },
    });

    if (!response.ok) {
      throw new Error(`BoE request failed: ${response.status}`);
    }

    // Get ZIP as ArrayBuffer
    const zipBuffer = await response.arrayBuffer();

    // For Vercel, we can use a more robust approach
    // Since Vercel runs Node.js, we can use libraries like 'adm-zip' or 'xlsx'
    // For now, we'll return an error message directing users to use the PHP import

    // Set CORS and caching headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Cache-Control', 'public, max-age=3600');
    res.setHeader('Content-Type', 'application/json');

    // TODO: Implement full ZIP/XLSX parsing with Node.js libraries
    // Recommended libraries: 'adm-zip', 'xlsx', or 'node-stream-zip'

    return res.status(200).json({
      error: 'ZIP parsing not yet implemented',
      message: 'Please use the PHP-based import in the plugin admin panel instead.',
      alternative: 'Upload the BoE ZIP file directly through Settings > UK Yield Rates > Import'
    });
  } catch (error) {
    console.error('Error fetching BoE data:', error);
    return res.status(500).json({
      error: 'Failed to fetch yield data',
      message: error.message
    });
  }
}
