PAPPERTEER_SKIP_CHROMIUM_DOWNLOAD=1;
const puppeteer = require('puppeteer-core');
const chromium = require('@sparticuz/chromium'); // Asegúrate de instalar esta librería

exports.handler = async (event) => {
    try {
        // Parsear el cuerpo del evento para obtener la URL
        const body = JSON.parse(event.body);  // Asegúrate de parsear el body
        const url = body.url;
        // Validar la URL
        if (!url || !/^https?:\/\/[^\s/$.?#].[^\s]*$/.test(url)) {
            return {
                statusCode: 200,
                body: JSON.stringify({ error: 'La URL proporcionada no es válida ' + url }),
            };
        }

        // Configuración para Puppeteer usando Chromium en AWS Lambda
        const browser = await puppeteer.launch({
            args: chromium.args,
            executablePath: await chromium.executablePath(),
            headless: chromium.headless,
        });

        const page = await browser.newPage();

        // Navegar a la URL proporcionada
        const response = await page.goto(url, { waitUntil: 'networkidle2' });

        if (!response || response.status() === 404) {
            await browser.close();
            return {
                statusCode: 200,
                body: JSON.stringify({ error: 'No se pudo cargar la URL proporcionada' }),
            };
        }

        // Esperar al selector necesario en la página
        await page.waitForSelector('.ace_text-layer');

        // Extraer el contenido del editor
        const textContent = await page.evaluate(async () => {
            const aceTextLayer = document.querySelector('.ace_text-layer');
            const aceScrollBar = document.querySelector('.ace_scrollbar');
            if (!aceTextLayer || !aceScrollBar) return 'No se encontró el editor o la barra de desplazamiento';

            let codeText = '';
            const lineHeight = 21; // Altura de desplazamiento
            const maxScrollTop = aceScrollBar.scrollHeight - aceScrollBar.clientHeight; // Máximo desplazamiento permitido

            // Comenzar el desplazamiento
            for (let scrollTop = 0; scrollTop <= maxScrollTop; scrollTop += lineHeight) {
                // Establecer el desplazamiento en la barra de desplazamiento
                aceScrollBar.scrollTop = scrollTop;

                // Espera para permitir que el contenido se renderice
                await new Promise(resolve => setTimeout(resolve, 200)); // Ajusta el tiempo si es necesario

                // Selecciona todas las líneas visibles
                const visibleLines = Array.from(aceTextLayer.querySelectorAll('.ace_line'))
                    .filter(line => {
                        const lineTop = parseInt(line.style.top);
                        return lineTop >= scrollTop && lineTop < scrollTop + lineHeight;
                    });

                // Si hay líneas visibles, agrega la última línea a codeText
                if (visibleLines.length > 0) {
                    const lastVisibleLineText = visibleLines[visibleLines.length - 1].innerText.trim();
                    codeText += lastVisibleLineText + '\n'; // Agrega la última línea visible al código
                }
            }

            // Espera un último momento para asegurarte de que todo el contenido se haya cargado
            await new Promise(resolve => setTimeout(resolve, 200));

            // Extrae el texto restante después del scroll
            const remainingCode = Array.from(aceTextLayer.querySelectorAll('.ace_line'))
                .map(line => line.textContent.trim())
                .join('\n');

            return codeText + remainingCode.trim();
        });


        // Cerrar el navegador
        await browser.close();

        // Responder con el contenido extraído
        return {
            statusCode: 200,
            body: JSON.stringify({ content: textContent }),
        };

    } catch (error) {
        // Manejar errores
        return {
            statusCode: 500,
            body: JSON.stringify({
                error: 'Ocurrió un error al procesar la solicitud',
                details: error.message,
            }),
        };
    }
};
