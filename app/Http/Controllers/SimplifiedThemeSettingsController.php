<?php

namespace App\Http\Controllers;

use App\Models\SimplifiedThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimplifiedThemeSettingsController extends Controller
{
    /**
     * Display the theme settings page
     */
    public function index()
    {
        // Check if user is admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied. Only administrators can manage theme settings.');
        }

        // Always get global theme (not company-specific)
        $theme = SimplifiedThemeSetting::active(null);
        
        // Get available presets
        $presets = SimplifiedThemeSetting::getPresets();
        
        \Log::info('Theme settings page loaded', [
            'preset_name' => $theme->preset_name,
            'preset_keys' => array_keys($presets)
        ]);
        
        return view('settings.simplified-theme', compact('theme', 'presets'));
    }

    /**
     * Update theme settings
     */
    public function update(Request $request)
    {
        // Check if user is admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        $validated = $request->validate([
            // Colors
            'primary_color' => 'required|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'accent_color' => 'required|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'danger_color' => 'required|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'text_color' => 'required|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'muted_text_color' => 'required|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'background_color' => 'required|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            // Typography
            'font_family' => 'required|in:system,inter,roboto,poppins,opensans',
            'font_size_base' => 'required|in:10px,11px,12px,13px,14px,15px,16px',
            'header_font_size' => 'required|in:small,normal,large',
            'line_height' => 'required|in:compact,normal,relaxed',
            // Header customization
            'header_title_size' => 'required|in:sm,base,lg,xl,2xl,3xl,4xl',
            'header_title_weight' => 'required|in:normal,medium,semibold,bold,extrabold',
            'header_padding' => 'required|in:compact,normal,relaxed,spacious',
            'header_spacing' => 'required|in:tight,normal,relaxed',
            // Buttons
            'button_size' => 'required|in:small,normal,large',
            'button_text_color' => 'required|in:white,black,auto',
            'button_radius' => 'required|in:none,small,medium,large,full',
            'button_style' => 'required|in:solid,outline,ghost',
            // Tables
            'table_row_padding' => 'required|in:compact,normal,spacious',
            'table_header_style' => 'required|in:light,dark,colored,bold',
            'table_striped' => 'nullable|boolean',
            'table_hover_effect' => 'required|in:none,light,dark,colored',
            // Layout
            'header_height' => 'required|in:compact,normal,tall',
            'sidebar_width' => 'required|in:narrow,normal,wide',
            'card_padding' => 'required|in:small,normal,large',
            'card_shadow' => 'required|in:none,small,medium,large',
            'border_radius' => 'required|in:none,small,medium,large',
            // Menu styling
            'sidebar_style' => 'nullable|in:light,dark,colored',
            'sidebar_background_color' => 'nullable|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'sidebar_text_color' => 'nullable|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'sidebar_active_color' => 'nullable|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'top_nav_style' => 'nullable|in:tabs,pills,underline',
            'top_nav_active_color' => 'nullable|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            'sidebar_icon_size' => 'nullable|in:small,medium,large',
            'sidebar_text_size' => 'nullable|in:small,medium,large',
            'topbar_background_color' => 'nullable|string|size:7|regex:/^#[0-9a-fA-F]{6}$/',
            // View header styling
            'view_header_title_size' => 'nullable|in:small,medium,large',
            'view_header_padding' => 'nullable|in:compact,normal,spacious',
            'view_header_auto_scale' => 'nullable|boolean',
        ]);

        // Always use global theme (company_id = NULL)
        $theme = SimplifiedThemeSetting::whereNull('company_id')
            ->where('is_active', true)
            ->first();
            
        if (!$theme) {
            $theme = SimplifiedThemeSetting::createDefault(null);
        }

        // Update theme
        $validated['table_striped'] = $request->has('table_striped');
        $validated['preset_name'] = null; // Clear preset when manually updating
        
        \Log::info('Theme update - before save', [
            'theme_id' => $theme->id,
            'current_font_size' => $theme->font_size_base,
            'new_font_size' => $validated['font_size_base'],
            'all_validated' => $validated
        ]);
        
        $theme->update($validated);
        
        \Log::info('Theme update - after save', [
            'theme_id' => $theme->id,
            'saved_font_size' => $theme->font_size_base,
            'fresh_font_size' => $theme->fresh()->font_size_base
        ]);
        
        // Explicitly clear cache to ensure immediate update
        $theme->clearCache();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme settings updated successfully',
            ]);
        }

        return redirect()->route('settings.theme')
            ->with('success', 'Theme settings updated successfully');
    }

    /**
     * Apply a preset
     */
    public function applyPreset(Request $request, $preset)
    {
        // Check if user is admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Validate preset exists
        $presets = SimplifiedThemeSetting::getPresets();
        if (!isset($presets[$preset])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid preset',
            ], 400);
        }

        // Always use global theme (company_id = NULL)
        $theme = SimplifiedThemeSetting::whereNull('company_id')
            ->where('is_active', true)
            ->first();
            
        if (!$theme) {
            $theme = SimplifiedThemeSetting::createDefault(null);
        }

        // Apply preset
        $theme->applyPreset($preset);

        return response()->json([
            'success' => true,
            'message' => 'Preset applied successfully',
            'theme' => $theme->fresh(),
        ]);
    }

    /**
     * Reset to default theme
     */
    public function reset()
    {
        // Check if user is admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Delete existing global theme
        SimplifiedThemeSetting::whereNull('company_id')
            ->where('is_active', true)
            ->delete();
        
        // Create new default global theme
        SimplifiedThemeSetting::createDefault(null);

        return redirect()->route('settings.theme')
            ->with('success', 'Theme reset to default successfully');
    }

    /**
     * Get preview of theme changes (AJAX)
     */
    public function preview(Request $request)
    {
        // Check if user is admin
        if (!in_array(Auth::user()->role, ['super_admin', 'admin'])) {
            abort(403, 'Access denied.');
        }

        // Create temporary theme object with request data
        $theme = new SimplifiedThemeSetting($request->all());
        
        return response()->json([
            'success' => true,
            'css' => $theme->getCssVariables(),
            'classes' => [
                'button' => $theme->getComponentClasses('button'),
                'card' => $theme->getComponentClasses('card'),
                'table' => $theme->getComponentClasses('table'),
                'header' => $theme->getComponentClasses('header'),
            ],
        ]);
    }
}