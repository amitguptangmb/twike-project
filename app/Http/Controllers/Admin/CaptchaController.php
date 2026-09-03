<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Port of CreateCaptcha.aspx.cs as used from adminlogin.aspx (same shared
 * captcha page the original app used for every portal's login, keyed here
 * under its own session key so an open partner/merchant login tab in the
 * same browser session can't collide with an open admin one - see
 * Partner\CaptchaController for the identical rationale there).
 */
class CaptchaController extends Controller
{
    private const ALPHABET = '012345679ACEFGHKLMNPRSWXZabcdefghijkhlmnopqrstuvwxyz';

    public function show(Request $request): Response
    {
        $code = '';
        for ($j = 0; $j <= 5; $j++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }
        $request->session()->put('admin_captcha', $code);

        // Original 200x60 canvas and 20 noise lines, unchanged - per
        // explicit user request, only the character font size is enlarged
        // (below), nothing about the page UI or overall image footprint.
        $width = 200;
        $height = 60;
        $image = imagecreatetruecolor($width, $height);

        $blue = imagecolorallocate($image, 100, 149, 237);
        $yellow = imagecolorallocate($image, 255, 255, 0);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $blue);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $yellow);

        // GD's imagestring() has no font-size parameter - font 5 (9x15px)
        // is its largest built-in bitmap font, which is what the original
        // used directly and is what made the text small. Bigger text here
        // means rendering each character into its own small 9x15 buffer,
        // then imagecopyresampled()-ing it up ~3x onto the main canvas -
        // same blue fill in the buffer so the enlarged glyph blends into
        // the background with no visible box edges.
        $counter = 8;
        foreach (str_split($code) as $char) {
            $glyph = imagecreatetruecolor(9, 15);
            imagefill($glyph, 0, 0, imagecolorallocate($glyph, 100, 149, 237));
            imagestring($glyph, 5, 0, 0, $char, imagecolorallocate($glyph, 0, 0, 0));
            imagecopyresampled($image, $glyph, $counter, 9, 0, 0, 28, 42, 9, 15);
            imagedestroy($glyph);
            $counter += 30;
        }

        for ($i = 0; $i < 20; $i++) {
            imageline(
                $image,
                random_int(0, 150), random_int(1, 59),
                random_int(0, 199), random_int(1, 59),
                $yellow
            );
        }

        ob_start();
        imagegif($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return response($bytes, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
