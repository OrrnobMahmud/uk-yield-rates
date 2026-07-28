# Sample BoE Yield Data Endpoints

These are ready-to-deploy scripts that fetch Bank of England gilt yield data and expose it as a JSON endpoint for the UK Yield Rates WordPress plugin.

## Why Use This?

The Bank of England publishes gilt yield data as CSV but has no API. These scripts:
- ✅ Fetch the official BoE CSV data automatically
- ✅ Parse the yield data for 2Y, 5Y, 10Y, 20Y, and 30Y maturities
- ✅ Expose it as a simple JSON endpoint
- ✅ Run on free tiers (no costs)
- ✅ Update automatically (you can set up daily triggers)

## Options

### Option 1: Cloudflare Workers (Recommended)

**Free tier:** 100,000 requests/day

1. Go to https://workers.cloudflare.com/
2. Sign up for a free account
3. Click "Create a Service"
4. Name it (e.g., "boe-yields")
5. Replace the default code with the contents of `cloudflare-worker.js`
6. Click "Save and Deploy"
7. Copy your worker URL (e.g., `https://boe-yields.your-name.workers.dev`)
8. Paste it in the WordPress plugin settings

**To set up daily updates (optional):**
1. Go to https://workers.cloudflare.com/
2. Click on your worker
3. Go to "Triggers" tab
4. Add a Cron Trigger: `0 8 * * *` (runs daily at 8am UTC)

### Option 2: Vercel

**Free tier:** 100,000 requests/month

1. Go to https://vercel.com/
2. Sign up with GitHub
3. Create a new project
4. Create a file: `api/yields.js`
5. Paste the contents of `vercel-function.js`
6. Deploy
7. Your endpoint: `https://your-project.vercel.app/api/yields`
8. Paste it in the WordPress plugin settings

**To set up daily updates (optional):**
1. Go to https://vercel.com/
2. Click on your project
3. Go to "Settings" → "Cron Jobs"
4. Add: `api/yields` with schedule `0 8 * * *`

### Option 3: Netlify Functions

**Free tier:** 125,000 requests/month

1. Go to https://netlify.com/
2. Sign up with GitHub
3. Create a new site
4. Create a file: `netlify/functions/yields.js`
5. Rename the export to match Netlify's format
6. Deploy
7. Your endpoint: `https://your-site.netlify.app/.netlify/functions/yields`
8. Paste it in the WordPress plugin settings

## Expected JSON Format

The endpoint should return JSON in this format:

```json
{
  "yields": {
    "2": {"yield": 4.25, "change": 0.02, "date": "2026-07-28"},
    "5": {"yield": 4.15, "change": -0.01, "date": "2026-07-28"},
    "10": {"yield": 4.05, "change": 0.00, "date": "2026-07-28"},
    "20": {"yield": 4.35, "change": 0.03, "date": "2026-07-28"},
    "30": {"yield": 4.45, "change": -0.02, "date": "2026-07-28"}
  }
}
```

## Testing Your Endpoint

After deploying, test it by visiting your endpoint URL in a browser. You should see JSON data like the example above.

## Troubleshooting

### "Could not parse CSV header" error
- The Bank of England may have changed their CSV format
- Check if the CSV URL is still valid
- Update the series codes if needed

### Empty yields object
- Check if the CSV has recent data
- Verify the date format matches what the script expects

### CORS errors
- Make sure your endpoint includes `Access-Control-Allow-Origin: *` header
- Both Cloudflare and Vercel samples include this by default

## Cost

**It's completely free!**

- Cloudflare Workers: 100,000 requests/day free
- Vercel: 100,000 requests/month free
- Netlify: 125,000 requests/month free

For a typical WordPress site with 1,000 visitors/day, you'd use ~30,000 requests/month (3 fetches per visitor). Well within free tiers!

## Security

- The endpoint is read-only (no sensitive data)
- You can add authentication if needed
- Consider rate limiting if you're worried about abuse

## Support

For issues with the sample scripts, check:
- Cloudflare Workers docs: https://developers.cloudflare.com/workers/
- Vercel docs: https://vercel.com/docs
- Bank of England data: https://www.bankofengland.co.uk/statistics/yield-curves
