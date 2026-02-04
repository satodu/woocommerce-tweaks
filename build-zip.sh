#!/bin/bash

PLUGIN_SLUG="tweaks-for-woocommerce"
SOURCE_DIR="woocommerce-tweaks"
ZIP_NAME="${PLUGIN_SLUG}.zip"

# Clean up previous build
echo "Cleaning up..."
rm -f "$ZIP_NAME"
rm -rf "build"

# Create build directory structure
mkdir -p "build/$PLUGIN_SLUG"
mkdir -p "wp-org-assets"

# Generate Asset (Icon)
if [ -f "imgs/logo.jpg" ]; then
    echo "Generating icon-256x256.jpg from imgs/logo.jpg..."
    if command -v convert &> /dev/null; then
        convert "imgs/logo.jpg" -resize 256x256\! "wp-org-assets/icon-256x256.jpg"
    elif command -v ffmpeg &> /dev/null; then
        ffmpeg -y -i "imgs/logo.jpg" -vf scale=256:256 "wp-org-assets/icon-256x256.jpg" &> /dev/null
    else
        echo "Warning: ImageMagick (convert) or ffmpeg not found. Copying original file."
        cp "imgs/logo.jpg" "wp-org-assets/icon-256x256.jpg"
    fi
else
    echo "Warning: imgs/logo.jpg not found."
fi

# Copy only the necessary files for distribution
echo "Copying files from $SOURCE_DIR..."
cp "$SOURCE_DIR/woocommerce-tweaks.php" "build/$PLUGIN_SLUG/"
cp "$SOURCE_DIR/README.md" "build/$PLUGIN_SLUG/"
cp -r "$SOURCE_DIR/admin" "build/$PLUGIN_SLUG/"

# Add index.php to prevent directory listing if it exists, or create a dummy one
if [ ! -f "build/$PLUGIN_SLUG/index.php" ]; then
    echo "<?php // Silence is golden." > "build/$PLUGIN_SLUG/index.php"
fi

# Zip it
echo "Zipping..."
cd build

if command -v zip &> /dev/null; then
    zip -r -q "../$ZIP_NAME" "$PLUGIN_SLUG"
    echo "Zipped using 'zip'."
elif command -v python3 &> /dev/null; then
    echo "'zip' command not found, falling back to python3..."
    python3 -c "import shutil; shutil.make_archive('../$PLUGIN_SLUG', 'zip', '.', '$PLUGIN_SLUG')"
else
    echo "Error: Neither 'zip' nor 'python3' found. Please install one of them."
    exit 1
fi

cd ..

# Cleanup build dir
rm -rf build

echo "Done! created $ZIP_NAME"
