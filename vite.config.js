import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import fs from 'fs-extra';
import { glob } from 'glob';
import copy from 'rollup-plugin-copy';
import postcss from 'postcss';
import rtlcss from 'rtlcss';

const folder = {
    src: "resources/", // Laravel's standard resources folder
    src_assets: "resources/assets/", 
    dist: "public/", 
    dist_assets: "public/build/"
};

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                folder.src_assets + 'scss/theme.scss',
                ...Object.values(generateJsEntries())
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources'),
        },
    },
    build: {
        manifest: true,
        outDir: path.resolve(__dirname, 'public/build'),
        emptyOutDir: true,
        // Ensure CSS is minified
        cssMinify: true,
        // Configure CSS options
        cssCodeSplit: true,
        rollupOptions: {
            external: [
                /^assets\/libs\//,
            ],
            output: {
                assetFileNames: (assetInfo) => {
                    const ext = assetInfo.name.split('.').pop();
                    if (ext === 'css') {
                        // Change CSS output to use .min.css extension
                        return 'css/[name].min.css';
                    } else if (/png|jpe?g|svg|mp3|gif|tiff|bmp|ico/i.test(ext)) {
                        return 'img/[name][extname]';
                    } else {
                        return '[name][extname]';
                    }
                },
                chunkFileNames: 'js/[name].js',
                entryFileNames: 'js/[name].js',
            },
            plugins: [
                {
                    name: 'generate-rtl-css',
                    async writeBundle(options, bundle) {
                        const cssFiles = Object.keys(bundle).filter(fileName => fileName.endsWith('.min.css'));
                        
                        for (const cssFile of cssFiles) {
                            const filePath = path.resolve(options.dir || 'public/build', cssFile);
                            
                            try {
                                // Read the CSS file
                                const css = await fs.readFile(filePath, 'utf8');
                                
                                // Process with rtlcss
                                const result = await postcss([rtlcss]).process(css, { from: filePath });
                                
                                // Generate RTL filename
                                const rtlFilePath = filePath.replace('.min.css', '.rtl.min.css');
                                
                                // Write the RTL CSS file
                                await fs.writeFile(rtlFilePath, result.css);
                                console.log(`Generated RTL CSS: ${rtlFilePath}`);
                            } catch (error) {
                                console.error(`Error generating RTL CSS for ${cssFile}:`, error);
                            }
                        }
                    }
                },
                {
                    name: 'copy-specific-packages',
                    async writeBundle() {
                        const outputPath = path.resolve(__dirname, folder.dist_assets);
                        const configPath = path.resolve(__dirname, 'package-libs-config.json');

                        try {
                            const configContent = await fs.readFile(configPath, 'utf-8');
                            const { packagesToCopy } = JSON.parse(configContent);

                            for (const packageName of packagesToCopy) {
                                const destPackagePath = path.join(outputPath, 'libs', packageName);
                                const sourcePath = path.join(__dirname, 'node_modules', packageName);

                                try {
                                    await fs.access(sourcePath, fs.constants.F_OK);
                                    await fs.copy(sourcePath, destPackagePath);
                                } catch (error) {
                                    console.error(`Package ${packageName} does not exist.`);
                                }
                            }
                        } catch (error) {
                            console.error('Error copying and renaming packages:', error);
                        }
                    },
                },
                // Manually copy folders using fs-extra after the build
                {
                    name: 'custom-copy-folders',
                    async writeBundle() {
                        // Map of source to destination folders
                        const foldersToCopy = [
                            { 
                                src: path.resolve(__dirname, folder.src_assets, 'app-icons'), 
                                dest: path.resolve(__dirname, folder.dist_assets, 'app-icons') 
                            },
                            { 
                                src: path.resolve(__dirname, folder.src_assets, 'fonts'), 
                                dest: path.resolve(__dirname, folder.dist_assets, 'fonts') 
                            },
                            { 
                                src: path.resolve(__dirname, folder.src_assets, 'icons'), 
                                dest: path.resolve(__dirname, folder.dist_assets, 'icons') 
                            },
                            { 
                                src: path.resolve(__dirname, folder.src_assets, 'img'), 
                                dest: path.resolve(__dirname, folder.dist_assets, 'img') 
                            },
                            { 
                                src: path.resolve(__dirname, folder.src_assets, 'js'), 
                                dest: path.resolve(__dirname, folder.dist_assets, 'js') 
                            },
                        ];
                        
                        // Copy all folders
                        for (const { src, dest } of foldersToCopy) {
                            try {
                                if (await fs.pathExists(src)) {
                                    console.log(`Copying from ${src} to ${dest}`);
                                    await fs.copy(src, dest, { 
                                        overwrite: true,
                                        recursive: true,
                                        errorOnExist: false 
                                    });
                                    console.log(`Successfully copied ${src}`);
                                } else {
                                    console.log(`Source directory ${src} does not exist, skipping`);
                                }
                            } catch (error) {
                                console.error(`Error copying folder ${src}:`, error);
                            }
                        }
                    }
                }
            ],
        },
    },
});

// Function to automatically discover JS entry points
function generateJsEntries() {
    const entries = {};
    // Look for JS files in Laravel's standard js directory
    const jsFiles = glob.sync('resources/js/**/*.js');
    jsFiles.forEach((file) => {
        const name = file.replace('resources/js/', '').replace('resources\\js\\', '').replace('.js', '');
        entries[name] = file;
    });
    return entries;
}