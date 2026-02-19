<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Auditor;
use App\Entity\Job;
use App\Enum\AuditorTimezone;
use App\Enum\JobStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $auditor1 = new Auditor();
        $auditor1->setName('John Doe');
        $auditor1->setEmail('john.doe@example.com');
        $auditor1->setOriginTimezone(AuditorTimezone::MADRID);
        $manager->persist($auditor1);

        $auditor2 = new Auditor();
        $auditor2->setName('Jane Smith');
        $auditor2->setEmail('jane.smith@example.com');
        $auditor2->setOriginTimezone(AuditorTimezone::MEXICO_CITY);
        $manager->persist($auditor2);

        $auditor3 = new Auditor();
        $auditor3->setName('Bob Johnson');
        $auditor3->setEmail('bob.johnson@example.com');
        $auditor3->setOriginTimezone(AuditorTimezone::LONDON);
        $manager->persist($auditor3);

        $jobTitles = [
            'Site Inspection A',
            'Site Inspection B',
            'Site Inspection C',
            'Building Safety Check',
            'Equipment Audit',
            'Fire Safety Inspection',
            'Electrical System Review',
            'Structural Assessment',
            'Environmental Compliance Check',
            'Security System Evaluation',
        ];

        $jobDescriptions = [
            'Initial inspection of the site',
            'Follow-up inspection',
            'Routine maintenance check',
            'Comprehensive safety audit',
            'Equipment functionality review',
            'Fire safety compliance verification',
            'Electrical system evaluation',
            'Structural integrity assessment',
            'Environmental standards compliance',
            'Security system performance review',
        ];

        for ($i = 0; $i < 10; $i++) {
            $job = new Job();
            $job->setTitle($jobTitles[$i]);
            $job->setDescription($jobDescriptions[$i]);
            $job->setStatus(JobStatus::OPEN);
            $manager->persist($job);
        }

        $manager->flush();
    }
}
