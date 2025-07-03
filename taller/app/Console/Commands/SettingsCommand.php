<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class SettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:manage
                            {action : Action to perform (get, set, list, clear, init)}
                            {key? : Setting key}
                            {value? : Setting value}
                            {--category= : Setting category}
                            {--type=string : Setting type}
                            {--force : Force action without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'get':
                return $this->getSetting();
            case 'set':
                return $this->setSetting();
            case 'list':
                return $this->listSettings();
            case 'clear':
                return $this->clearCache();
            case 'init':
                return $this->initializeSettings();
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }
    }

    /**
     * Get a setting value
     */
    private function getSetting()
    {
        $key = $this->argument('key');
        
        if (!$key) {
            $this->error('Key is required for get action');
            return 1;
        }

        $value = Setting::get($key);
        
        if ($value === null) {
            $this->warn("Setting '{$key}' not found");
            return 1;
        }

        $this->info("Value for '{$key}': " . (is_array($value) ? json_encode($value) : $value));
        return 0;
    }

    /**
     * Set a setting value
     */
    private function setSetting()
    {
        $key = $this->argument('key');
        $value = $this->argument('value');
        
        if (!$key || $value === null) {
            $this->error('Key and value are required for set action');
            return 1;
        }

        $category = $this->option('category') ?? 'general';
        $type = $this->option('type') ?? 'string';

        // Parse value based on type
        $parsedValue = $this->parseValue($value, $type);

        Setting::set($key, $parsedValue, $category, $type);
        
        $this->info("Setting '{$key}' set to: " . (is_array($parsedValue) ? json_encode($parsedValue) : $parsedValue));
        return 0;
    }

    /**
     * List all settings
     */
    private function listSettings()
    {
        $category = $this->option('category');
        
        if ($category) {
            $settings = Setting::where('category', $category)->get();
            $this->info("Settings for category '{$category}':");
        } else {
            $settings = Setting::all();
            $this->info('All settings:');
        }

        if ($settings->isEmpty()) {
            $this->warn('No settings found');
            return 0;
        }

        $headers = ['Key', 'Value', 'Category', 'Type', 'Public'];
        $rows = [];

        foreach ($settings as $setting) {
            $value = $setting->value;
            if (strlen($value) > 50) {
                $value = substr($value, 0, 47) . '...';
            }
            
            $rows[] = [
                $setting->key,
                $value,
                $setting->category,
                $setting->type,
                $setting->is_public ? 'Yes' : 'No'
            ];
        }

        $this->table($headers, $rows);
        return 0;
    }

    /**
     * Clear settings cache
     */
    private function clearCache()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to clear settings cache?')) {
            $this->info('Cancelled');
            return 0;
        }

        Setting::clearCache();
        $this->info('Settings cache cleared successfully');
        return 0;
    }

    /**
     * Initialize default settings
     */
    private function initializeSettings()
    {
        if (!$this->option('force') && !$this->confirm('This will initialize default settings. Continue?')) {
            $this->info('Cancelled');
            return 0;
        }

        $this->info('Initializing default settings...');
        
        Setting::initializeDefaults();
        
        $this->info('Default settings initialized successfully');
        
        // Show summary
        $total = Setting::count();
        $categories = Setting::distinct('category')->count();
        
        $this->info("Total settings: {$total}");
        $this->info("Categories: {$categories}");
        
        return 0;
    }

    /**
     * Parse value based on type
     */
    private function parseValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            
            case 'integer':
                return (int) $value;
            
            case 'float':
                return (float) $value;
            
            case 'array':
            case 'json':
                return json_decode($value, true) ?? [];
            
            case 'string':
            default:
                return $value;
        }
    }
}