const puppeteer = require('puppeteer');
const fs = require('fs');

(async () => {
    const browser = await puppeteer.launch({ headless: 'new' });
    const page = await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });
    
    const results = {
        login: "FAIL",
        dashboard: "FAIL",
        transaksi: "FAIL",
        anggaran: "FAIL",
        laporan: "FAIL",
        pengaturan: "FAIL"
    };

    try {
        console.log("Navigating to login...");
        await page.goto('http://localhost/FinanceTrack/login.php', {waitUntil: 'domcontentloaded'});
        
        await page.type('input[name="email"]', 'demo@financetrack.test');
        await page.type('input[name="password"]', 'demo123'); 
        await page.click('button[type="submit"]');
        
        console.log("Waiting for navigation...");
        await page.waitForNavigation({waitUntil: 'domcontentloaded'});
        if (page.url().includes('dashboard.php')) {
            results.login = "PASS";
            results.dashboard = "PASS";
        }
        
        console.log("Checking transaksi...");
        await page.goto('http://localhost/FinanceTrack/pages/transaksi.php', {waitUntil: 'domcontentloaded'});
        if (page.url().includes('transaksi.php')) results.transaksi = "PASS";
        
        console.log("Checking anggaran...");
        await page.goto('http://localhost/FinanceTrack/pages/anggaran.php', {waitUntil: 'domcontentloaded'});
        if (page.url().includes('anggaran.php')) results.anggaran = "PASS";
        
        console.log("Checking laporan...");
        await page.goto('http://localhost/FinanceTrack/pages/laporan.php', {waitUntil: 'domcontentloaded'});
        if (page.url().includes('laporan.php')) results.laporan = "PASS";

        console.log("Checking pengaturan...");
        await page.goto('http://localhost/FinanceTrack/pages/pengaturan.php', {waitUntil: 'domcontentloaded'});
        if (page.url().includes('pengaturan.php')) results.pengaturan = "PASS";

        console.log("Regression Test Results:", results);
        fs.writeFileSync('regression_d3.json', JSON.stringify(results, null, 2));
    } catch (e) {
        console.error("Test error:", e);
    } finally {
        await browser.close();
    }
})();
