<?php

namespace App\Twig;

use App\Controller\AdminSettingsController;
use App\Repository\SettingRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    public function __construct(private readonly SettingRepository $settings)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('donations_enabled', $this->isDonationsEnabled(...)),
        ];
    }

    public function isDonationsEnabled(): bool
    {
        return $this->settings->getBool(AdminSettingsController::DONATIONS_ENABLED_SETTING_KEY, true);
    }
}
