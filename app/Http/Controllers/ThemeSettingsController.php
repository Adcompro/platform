<?php

namespace App\Http\Controllers;

use App\Models\ThemeSetting;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ThemeSettingsController extends Controller
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Display theme settings page
     */
    public function index()
    {
        // Check authorization
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized. Only administrators can manage theme settings.');
        }

        // Check if theme customization is enabled
        if (!$this->themeService->isCustomizationEnabled()) {
            return redirect()->route('settings.index')
                ->with('error', 'Theme customization is not enabled. Please activate the Theme Customization plugin.');
        }

        $theme = ThemeSetting::active();
        $presets = ThemeSetting::getPresets();

        return view('settings.theme', compact('theme', 'presets'));
    }

    /**
     * Update theme settings
     */
    public function update(Request $request)
    {
        // Check authorization
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized. Only administrators can manage theme settings.');
        }

        $validated = $request->validate([
            'brand_name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:7',
            'primary_hover' => 'required|string|max:7',
            'primary_text' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'secondary_hover' => 'required|string|max:7',
            'secondary_border' => 'required|string|max:7',
            'accent_color' => 'required|string|max:7',
            'accent_hover' => 'required|string|max:7',
            // Button colors
            'button_primary_bg' => 'nullable|string|max:7',
            'button_primary_hover' => 'nullable|string|max:7',
            'button_primary_text' => 'nullable|string|max:7',
            'button_secondary_bg' => 'nullable|string|max:7',
            'button_secondary_hover' => 'nullable|string|max:7',
            'button_secondary_text' => 'nullable|string|max:7',
            'button_secondary_border' => 'nullable|string|max:7',
            'button_danger_bg' => 'nullable|string|max:7',
            'button_danger_hover' => 'nullable|string|max:7',
            'button_success_bg' => 'nullable|string|max:7',
            'button_success_hover' => 'nullable|string|max:7',
            // Link colors
            'link_color' => 'nullable|string|max:7',
            'link_hover' => 'nullable|string|max:7',
            'link_visited' => 'nullable|string|max:7',
            // Text colors
            'text_primary' => 'required|string|max:7',
            'text_secondary' => 'required|string|max:7',
            'text_muted' => 'required|string|max:7',
            // Typography
            'font_family' => 'nullable|string|max:255',
            'font_size_base' => 'required|string|max:10',
            'font_size_small' => 'required|string|max:10',
            'font_size_large' => 'required|string|max:10',
            'font_size_xl' => 'required|string|max:10',
            'font_weight_normal' => 'nullable|string|max:10',
            'font_weight_medium' => 'nullable|string|max:10',
            'font_weight_semibold' => 'nullable|string|max:10',
            'font_weight_bold' => 'nullable|string|max:10',
            // Spacing
            'letter_spacing_tight' => 'nullable|string|max:10',
            'letter_spacing_normal' => 'nullable|string|max:10',
            'letter_spacing_wide' => 'nullable|string|max:10',
            'line_height_tight' => 'nullable|string|max:10',
            'line_height_normal' => 'nullable|string|max:10',
            'line_height_loose' => 'nullable|string|max:10',
            'spacing_xs' => 'nullable|string|max:10',
            'spacing_sm' => 'nullable|string|max:10',
            'spacing_md' => 'nullable|string|max:10',
            'spacing_lg' => 'nullable|string|max:10',
            'spacing_xl' => 'nullable|string|max:10',
            // Button styling
            'border_radius' => 'nullable|string|max:20',
            'button_padding' => 'nullable|string|max:20',
            'header_padding' => 'nullable|string|max:20',
            'card_padding' => 'nullable|string|max:20',
            'transition_speed' => 'nullable|string|max:10',
            // Component colors
            'badge_bg_color' => 'required|string|max:7',
            'badge_text_color' => 'required|string|max:7',
            'success_color' => 'required|string|max:7',
            'warning_color' => 'required|string|max:7',
            'danger_color' => 'required|string|max:7',
            'info_color' => 'required|string|max:7',
            'table_header_bg' => 'required|string|max:7',
            'table_row_hover' => 'required|string|max:7',
            'table_border_color' => 'required|string|max:7',
            // Effects
            'use_shadows' => 'boolean',
            'use_gradients' => 'boolean',
            'use_animations' => 'boolean',
            'custom_css' => 'nullable|string|max:10000',
        ]);

        $theme = ThemeSetting::active();
        
        // Convert checkboxes
        $validated['use_shadows'] = $request->has('use_shadows');
        $validated['use_gradients'] = $request->has('use_gradients');
        $validated['use_animations'] = $request->has('use_animations');

        $theme->update($validated);

        // Clear cache
        Cache::forget('active_theme');

        return redirect()->route('theme.settings')
            ->with('success', 'Theme settings updated successfully!');
    }

    /**
     * Apply a preset theme
     */
    public function applyPreset(Request $request)
    {
        // Check authorization
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized. Only administrators can manage theme settings.');
        }

        $request->validate([
            'preset' => 'required|string|in:progress,progress_classic,modern,corporate,minimal,vibrant,tech_green'
        ]);

        $theme = ThemeSetting::active();
        
        if ($theme->applyPreset($request->preset)) {
            return redirect()->route('theme.settings')
                ->with('success', 'Theme preset applied successfully!');
        }

        return redirect()->route('theme.settings')
            ->with('error', 'Failed to apply theme preset.');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        // Check authorization
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized. Only administrators can manage theme settings.');
        }

        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        $theme = ThemeSetting::active();

        // Delete old logo if exists
        if ($theme->logo_path && Storage::exists($theme->logo_path)) {
            Storage::delete($theme->logo_path);
        }

        // Store new logo
        $path = $request->file('logo')->store('logos', 'public');
        
        $theme->update(['logo_path' => $path]);

        return redirect()->route('theme.settings')
            ->with('success', 'Logo uploaded successfully!');
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon(Request $request)
    {
        // Check authorization
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized. Only administrators can manage theme settings.');
        }

        $request->validate([
            'favicon' => 'required|image|mimes:ico,png|max:512'
        ]);

        $theme = ThemeSetting::active();

        // Delete old favicon if exists
        if ($theme->favicon_path && Storage::exists($theme->favicon_path)) {
            Storage::delete($theme->favicon_path);
        }

        // Store new favicon
        $path = $request->file('favicon')->store('favicons', 'public');
        
        $theme->update(['favicon_path' => $path]);

        return redirect()->route('theme.settings')
            ->with('success', 'Favicon uploaded successfully!');
    }

    /**
     * Reset to default theme
     */
    public function reset()
    {
        // Check authorization
        if (!in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Unauthorized. Only administrators can manage theme settings.');
        }

        $theme = ThemeSetting::active();
        $theme->applyPreset('progress');

        // Reset other fields to defaults
        $theme->update([
            'brand_name' => 'Progress Communications',
            'custom_css' => null,
            'use_shadows' => true,
            'use_gradients' => false,
            'use_animations' => true,
        ]);

        Cache::forget('active_theme');

        return redirect()->route('theme.settings')
            ->with('success', 'Theme reset to default successfully!');
    }

    /**
     * Live preview (AJAX)
     */
    public function preview(Request $request)
    {
        $themeData = $request->all();
        
        // Generate preview CSS
        $previewCss = "
            :root {
                --primary-color: {$themeData['primary_color']};
                --primary-hover: {$themeData['primary_hover']};
                --accent-color: {$themeData['accent_color']};
                --text-primary: {$themeData['text_primary']};
                /* etc... */
            }
        ";

        return response()->json([
            'success' => true,
            'css' => $previewCss
        ]);
    }
}