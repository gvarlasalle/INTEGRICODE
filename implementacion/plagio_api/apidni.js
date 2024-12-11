const express = require('express');
const puppeteer = require('puppeteer');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware para permitir solicitudes JSON
app.use(express.json());

// Endpoint para buscar datos por DNI
app.post('/api/dni', async (req, res) => {
    const { dni } = req.body; // Se espera que el DNI venga en el cuerpo de la solicitud

    if (!dni) {
        return res.status(400).json({ error: 'DNI is required' });
    }

    try {
        const browser = await puppeteer.launch({
            headless: false,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        }); // Cambia a false si deseas ver el navegador
        const page = await browser.newPage();

        // Navega a la página del formulario
        await page.goto('https://eldni.com/pe/buscar-por-dni');

        // Espera a que el input de DNI esté visible
        await page.waitForSelector('#dni');

        // Escribe el DNI en el campo de entrada
        await page.type('#dni', dni);

        // Haz clic en el botón de búsqueda
        await Promise.all([
            page.click('#btn-buscar-por-dni'),
            page.waitForNavigation({ waitUntil: 'networkidle0' }) // Espera a que la navegación termine
        ]);
        // Espera a que el contenido se cargue
        await page.waitForSelector('#nombres');
        await page.waitForSelector('#apellidop');
        await page.waitForSelector('#apellidom');

        // Extrae los datos deseados
        const nombres = await page.$eval('#nombres', el => el.value.trim());
        const apellidoPaterno = await page.$eval('#apellidop', el => el.value.trim());
        const apellidoMaterno = await page.$eval('#apellidom', el => el.value.trim());

        await browser.close();

        // Devuelve los datos extraídos
        res.json({
            nombres,
            apellidoPaterno,
            apellidoMaterno
        });
    } catch (error) {
        console.error(error);
        res.status(500).json({ error: 'Failed to scrape data' });
    }
});

// Inicia el servidor
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
