# Img2Webp

**Version:** 1.0.0
**Requires:** ImageMagick installed on the server, reachable as `magick` or `convert` on the PATH (or wherever `$conf['ext_imagick_dir']` points). The admin page shows whether it was found before letting you upload.

## What it does

Adds its own upload page, separate from Piwigo's native "Add Photos," that converts each image to WebP (via ImageMagick, auto-orienting first) before handing it off to Piwigo's normal `add_uploaded_file()`. From that point on it's a completely ordinary Piwigo photo — derivatives, metadata, everything — just smaller and faster to load than the original upload. Piwigo's native uploader is left untouched; this is purely an additional option.

## Admin settings

**Plugins → Img2Webp**

| Field | What it does |
|---|---|
| Quality | 1–100, default 82. Passed straight to ImageMagick's `-quality`. |

## Usage

1. Go to **Plugins → Img2Webp**.
2. Pick one or more image files (JPEG, PNG, GIF, or WebP source — anything `getimagesize()` recognizes as one of those).
3. Optionally choose a target category.
4. Upload. Each file gets a per-file success/failure result (upload error, unsupported type, conversion failure, or registration failure are all reported separately, so a batch of 10 with one bad file doesn't block the other 9).

## Notes

- If ImageMagick isn't found, the upload form doesn't appear at all — only the status message explaining why.
- Detection deliberately does **not** reuse Piwigo's own `is_ext_imagick()` — that shells out with a POSIX `command -v` builtin that doesn't exist on Windows, so it always falls through to `convert`, which then hits Windows' own unrelated `convert.exe` instead of ImageMagick's. This plugin checks the actual version-string output instead, so detection works correctly on both the Linux hosts this ships to and a Windows dev machine.
