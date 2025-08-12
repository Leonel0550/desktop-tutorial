document.getElementById('open-camera').addEventListener('click', function () {
    const modal = document.getElementById('barcode-modal');
    modal.style.display = 'block'; // Asegúrate de que el modal esté visible
    // Usar un pequeño retraso puede asegurar que el modal esté completamente renderizado
    setTimeout(startScanner, 100);

    function startScanner() {
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#camera-preview') // Asegúrate de que este contenedor sea visible y tenga tamaño
            },
            decoder: {
                readers: ["code_128_reader"] // Puedes agregar más lectores si es necesario
            }
        }, function (err) {
            if (err) {
                console.error("Error de QuaggaJS: ", err);
                return;
            }
            Quagga.start();
        });

        Quagga.onDetected(function (data) {
            console.log("Código detectado: ", data.codeResult.code);
            document.getElementById('result').textContent = data.codeResult.code;
            document.querySelector('textarea[name="tracking"]').value = data.codeResult.code;
            document.getElementById('barcode-modal').style.display = 'none';
            Quagga.stop();
        });
    }

    // Cerrar modal
    document.querySelector('.close').addEventListener('click', function () {
        const modal = document.getElementById('barcode-modal');
        modal.style.display = 'none'; // Oculta el modal
        Quagga.stop(); // Detiene QuaggaJS
    });

    // Validación para valores no negativos en el formulario
    document.getElementById('piezas').addEventListener('input', function (e) {
        validarNoNegativo(e.target);
    });
    document.getElementById('altura').addEventListener('input', function (e) {
        validarNoNegativo(e.target);
    });
    document.getElementById('ancho').addEventListener('input', function (e) {
        validarNoNegativo(e.target);
    });
    document.getElementById('largo').addEventListener('input', function (e) {
        validarNoNegativo(e.target);
    });
    document.getElementById('peso').addEventListener('input', function (e) {
        validarNoNegativo(e.target);
    });

    function validarNoNegativo(input) {
        const value = input.value;
        if (value < 0) {
            input.setCustomValidity('El valor no puede ser negativo');
        } else {
            input.setCustomValidity('');
        }
    }
});
function simulateBarcodeDetection(barcode) {
    document.getElementById('result').textContent = barcode;
    document.querySelector('textarea[name="tracking"]').value = barcode;
    document.getElementById('barcode-modal').style.display = 'none';
}

// Simula la detección de un código de barras
simulateBarcodeDetection('123456789012');