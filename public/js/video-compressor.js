// Video Compression Utility
// Uses MediaRecorder API for client-side video compression

window.VideoCompressor = {
    /**
     * Compress video file
     * @param {File} file - Video file to compress
     * @param {Object} options - Compression options
     * @returns {Promise<{blob: Blob, metadata: Object}>}
     */
    async compress(file, options = {}) {
        const {
            maxWidth = 1280,
            maxHeight = 720,
            videoBitrate = 2500000, // 2.5 Mbps
            quality = 0.8,
            onProgress = null
        } = options;

        // Skip compression for small files (< 5MB)
        if (file.size < 5 * 1024 * 1024) {
            return {
                blob: file,
                metadata: {
                    originalSize: file.size,
                    compressedSize: file.size,
                    compressionRatio: 0,
                    skipped: true,
                    reason: 'File already small'
                }
            };
        }

        const originalSize = file.size;

        try {
            if (onProgress) onProgress('Loading video...', 10);

            // Create video element
            const video = document.createElement('video');
            video.preload = 'metadata';
            video.muted = true;
            video.playsInline = true;
            video.src = URL.createObjectURL(file);

            await new Promise((resolve, reject) => {
                video.onloadedmetadata = resolve;
                video.onerror = () => reject(new Error('Failed to load video'));
            });

            if (onProgress) onProgress('Analyzing video...', 20);

            // Calculate dimensions maintaining aspect ratio
            let width = video.videoWidth;
            let height = video.videoHeight;

            if (width > maxWidth || height > maxHeight) {
                const ratio = Math.min(maxWidth / width, maxHeight / height);
                width = Math.floor(width * ratio);
                height = Math.floor(height * ratio);
                // Ensure dimensions are even (required for some codecs)
                width = width % 2 === 0 ? width : width - 1;
                height = height % 2 === 0 ? height : height - 1;
            }

            if (onProgress) onProgress('Preparing to compress...', 30);

            // Create canvas for video processing
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');

            // Try to get stream with audio
            const stream = canvas.captureStream(30); // 30 fps

            // Check for MediaRecorder support
            let mimeType = 'video/webm;codecs=vp8,opus';
            if (!MediaRecorder.isTypeSupported(mimeType)) {
                mimeType = 'video/webm';
            }

            const chunks = [];
            const mediaRecorder = new MediaRecorder(stream, {
                mimeType: mimeType,
                videoBitsPerSecond: videoBitrate
            });

            mediaRecorder.ondataavailable = (e) => {
                if (e.data.size > 0) {
                    chunks.push(e.data);
                }
            };

            if (onProgress) onProgress('Compressing video...', 40);

            // Start recording
            mediaRecorder.start(100); // Collect data every 100ms
            video.currentTime = 0;
            await video.play();

            let lastProgress = 40;

            // Draw frames
            const drawFrame = () => {
                if (!video.paused && !video.ended) {
                    ctx.drawImage(video, 0, 0, width, height);
                    
                    // Update progress
                    if (onProgress && video.duration) {
                        const progress = 40 + Math.floor((video.currentTime / video.duration) * 50);
                        if (progress > lastProgress) {
                            lastProgress = progress;
                            onProgress(`Compressing... ${Math.floor((video.currentTime / video.duration) * 100)}%`, progress);
                        }
                    }
                    
                    requestAnimationFrame(drawFrame);
                }
            };
            drawFrame();

            // Wait for video to finish
            await new Promise((resolve) => {
                video.onended = resolve;
            });

            // Stop recording
            mediaRecorder.stop();

            // Wait for all data
            await new Promise((resolve) => {
                mediaRecorder.onstop = resolve;
            });

            if (onProgress) onProgress('Finalizing...', 95);

            // Create compressed blob
            const compressedBlob = new Blob(chunks, { type: mimeType });
            
            // Clean up
            URL.revokeObjectURL(video.src);

            const compressedSize = compressedBlob.size;
            const compressionRatio = Math.round(((originalSize - compressedSize) / originalSize) * 100);

            if (onProgress) onProgress('Complete!', 100);

            return {
                blob: compressedBlob,
                metadata: {
                    originalSize,
                    compressedSize,
                    compressionRatio,
                    originalDimensions: `${video.videoWidth}x${video.videoHeight}`,
                    compressedDimensions: `${width}x${height}`,
                    skipped: false
                }
            };

        } catch (error) {
            console.error('Video compression error:', error);
            // Fallback: return original file
            return {
                blob: file,
                metadata: {
                    originalSize: file.size,
                    compressedSize: file.size,
                    compressionRatio: 0,
                    skipped: true,
                    reason: 'Compression failed: ' + error.message
                }
            };
        }
    },

    /**
     * Format file size to human readable
     * @param {number} bytes
     * @returns {string}
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },

    /**
     * Check if browser supports video compression
     * @returns {boolean}
     */
    isSupported() {
        return !!(window.MediaRecorder && HTMLCanvasElement.prototype.captureStream);
    }
};
