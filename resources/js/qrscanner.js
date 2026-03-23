import { BrowserQRCodeReader } from '@zxing/browser';
import { NotFoundException } from '@zxing/library';

// Function to initialize QR code scanning
function initQRCodeScanning() {
    // Create a new instance of BrowserQRCodeReader
    const codeReader = new BrowserQRCodeReader();

    // Get all elements with class 'scan-qr'
    const qrElements = document.querySelectorAll('.scan-qr');

    // Add click event listener to each element
    qrElements.forEach(qrElement => {
        qrElement.addEventListener('click', async function () {
            // Ensure the application is served over HTTPS
            if (window.location.protocol !== 'https:') {
                alert(_lang.scanner_https_camera_notification);
                return;
            }

            try {
                // Check if the device supports scanning a QR code
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoInputDevices = devices.filter(device => device.kind === 'videoinput');

                if (videoInputDevices.length === 0) {
                    alert(_lang.no_scanner_notification);
                    return;
                }

                // Get the video element
                const video = document.getElementById('video');

                // Show the video element
                video.style.display = '';

                // Get all elements with class 'hide-on-scan' and 'disable-on-scan'
                const hideOnScanElements = document.querySelectorAll('.hide-on-scan');
                const disableOnScanElements = document.querySelectorAll('.disable-on-scan');

                // Try to get the back camera using the facingMode constraint
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                    .then(stream => {
                        handleStream(stream);
                    })
                    .catch(error => {
                        console.warn("Couldn't access the back camera:", error);

                        // If failed, try to access the default camera without specifying the facingMode
                        navigator.mediaDevices.getUserMedia({ video: true })
                            .then(stream => {
                                handleStream(stream);
                            })
                            .catch(console.error);
                    });

                function handleStream(stream) {
                    video.srcObject = stream;
                    video.play();

                    // Hide all elements with class 'hide-on-scan' and disable all elements with class 'disable-on-scan'
                    hideOnScanElements.forEach(el => el.style.display = 'none');
                    disableOnScanElements.forEach(el => el.disabled = true);

                    // Start decoding from the video element
                    codeReader.decodeFromVideoElement(video, (result, error, controls) => {
                        if (result) {
                            // If a QR code is found, verify it is a URL and then redirect to that URL
                            if (isValidURL(result.getText())) {
                                // Get the element with id 'code-found' and remove the 'hidden' class
                                const codeFound = document.getElementById('code-found');
                                if (codeFound) codeFound.classList.remove('hidden');

                                video.style.display = 'none';

                                // Show all elements with class 'hide-on-scan' and enable all elements with class 'disable-on-scan'
                                hideOnScanElements.forEach(el => el.style.display = '');
                                disableOnScanElements.forEach(el => el.disabled = false);

                                window.location.href = result.getText();
                            }
                        }

                        if (error && !(error instanceof NotFoundException)) {
                            console.error(error);
                            controls.stop();

                            // Show all elements with class 'hide-on-scan'
                            hideOnScanElements.forEach(el => el.style.display = '');
                        }                        
                    }).catch(console.error);
                }

            } catch (error) {
                console.error(error);
            }
        });
    });
}

// Function to check if a string is a valid URL
function isValidURL(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

// Initialize QR code scanning
initQRCodeScanning();
