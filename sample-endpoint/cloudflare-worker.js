/**
 * Cloudflare Worker for Bank of England Gilt Yields
 *
 * This worker fetches gilt yield data from Bank of England ZIP archive
 * and exposes it as a JSON endpoint for the UK Yield Rates WordPress plugin.
 *
 * Deploy to Cloudflare Workers (free tier: 100,000 requests/day)
 *
 * Usage:
 * 1. Go to https://workers.cloudflare.com/
 * 2. Create a new worker
 * 3. Paste this code
 * 4. Deploy
 * 5. Copy your worker URL and paste it in the plugin settings
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

export default {
  async fetch(request, env, ctx) {
    // Bank of England yield curve data URL (ZIP archive)
    const zipUrl = 'https://www.bankofengland.co.uk/-/media/boe/files/statistics/yield-curves/latest-yield-curve-data.zip';

    // Target maturities in years
    const targetMaturities = [2, 5, 10, 20, 30];

    // Maximum rows to scan for headers
    const maxHeaderScanRows = 10;

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

      // Parse ZIP and extract yields
      const yields = await parseBoEZip(zipBuffer, targetMaturities, maxHeaderScanRows);

      return new Response(JSON.stringify({ yields }), {
        headers: {
          'Content-Type': 'application/json',
          'Access-Control-Allow-Origin': '*',
          'Cache-Control': 'public, max-age=3600', // Cache for 1 hour
        },
      });
    } catch (error) {
      console.error('Error fetching BoE data:', error);
      return new Response(JSON.stringify({
        error: 'Failed to fetch yield data',
        message: error.message
      }), {
        status: 500,
        headers: { 'Content-Type': 'application/json' },
      });
    }
  },
};

/**
 * Parse Bank of England ZIP archive and extract yields
 *
 * Note: Cloudflare Workers don't have native ZIP support.
 * This implementation uses a minimal ZIP parser for the specific BoE format.
 * For production use, consider using a ZIP library or pre-parsed data.
 */
async function parseBoEZip(zipBuffer, targetMaturities, maxHeaderScanRows) {
  // For Cloudflare Workers, we'll use a simplified approach:
  // The BoE ZIP contains xlsx files which are essentially ZIP archives with XML.
  // We need to:
  // 1. Find the GLC Nominal workbook in the ZIP
  // 2. Extract the workbook (which is another ZIP)
  // 3. Parse the XML inside

  // Since CF Workers don't have full ZIP support, we'll implement a minimal parser
  // that can handle the specific structure of BoE xlsx files.

  // This is a simplified implementation that:
  // 1. Reads the central directory to find files
  // 2. Extracts the GLC Nominal workbook
  // 3. Parses the xlsx XML to find yield data

  const yields = {};

  // For now, return a placeholder structure
  // In production, implement full ZIP/XLSX parsing or use a pre-processed endpoint

  // TODO: Implement full ZIP/XLSX parsing
  // The current implementation requires additional libraries or a different approach

  throw new Error('ZIP parsing not yet implemented in Cloudflare Worker. Use the PHP-based import instead.');
}

/**
 * Parse xlsx XML content and extract yields
 */
function parseXlsxXml(xmlContent, targetMaturities) {
  const yields = {};

  // Parse XML to find yield curve data
  // Look for the "1. yield curve" sheet
  // Find header row with "years:" label
  // Map maturity columns
  // Extract latest data row

  // This is a placeholder - implement full XML parsing
  return yields;
}
