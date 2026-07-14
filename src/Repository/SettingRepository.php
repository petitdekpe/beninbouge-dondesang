<?php

namespace App\Repository;

use App\Entity\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Setting>
 */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function getValue(string $key): ?string
    {
        return $this->findOneBy(['settingKey' => $key])?->getValue();
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->getValue($key);

        return $value !== null && $value !== '' ? (int) $value : $default;
    }

    public function getBool(string $key, bool $default = true): bool
    {
        $value = $this->getValue($key);

        return $value !== null && $value !== '' ? $value === '1' : $default;
    }

    public function setValue(EntityManagerInterface $em, string $key, ?string $value): void
    {
        $setting = $this->findOneBy(['settingKey' => $key]);
        if (!$setting) {
            $setting = new Setting();
            $setting->setSettingKey($key);
        }
        $setting->setValue($value);

        $em->persist($setting);
        $em->flush();
    }
}
