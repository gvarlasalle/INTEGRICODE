const express = require('express');
const puppeteer = require('puppeteer');
const app = express();
const port = 3000; // Puedes cambiar el puerto si es necesario

app.use(express.json());

app.post('/extract-code', async (req, res) => {
    
    const { url } = req.body;

    if (!url) {
        return res.status(400).json({ error: 'Por favor, proporciona una URL en el cuerpo de la solicitud.' });
    }

    try {
        // Inicia el navegador
        const browser = await puppeteer.launch({ 
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        }); // Cambia a `false` si necesitas ver el navegador
        const page = await browser.newPage();

        // Navega a la página proporcionada
        await page.goto(url, { waitUntil: 'networkidle2' });

        // Espera a que el elemento de texto esté visible
        await page.waitForSelector('.ace_text-layer');

        // Extrae el código fuente
        const code = await page.evaluate(() => {
            // Selecciona el contenedor de texto
            const aceTextLayer = document.querySelector('.ace_text-layer');
            if (!aceTextLayer) return 'No se encontró la capa de texto de código';

            let codeText = '';

            // Extrae el texto de cada línea
            const lines = aceTextLayer.querySelectorAll('.ace_line');
            lines.forEach(line => {
                codeText += line.textContent.trim() + '\n';
            });

            return codeText.trim();
        });

        // Cierra el navegador
        await browser.close();

        // Envía el código extraído como respuesta
        res.json({ code });
    } catch (error) {
        res.status(500).json({ error: 'Error al extraer el código.', details: error.message });
    }
});

app.listen(port, () => {
    console.log(`Servidor escuchando en http://localhost:${port}`);
});
