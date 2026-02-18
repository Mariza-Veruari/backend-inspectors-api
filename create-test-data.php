<?php

/**
 * Quick script to create test data for the Inspector Scheduling API
 * 
 * Usage: php create-test-data.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Entity\Inspector;
use App\Entity\Job;
use App\Entity\JobStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Bootstrap Symfony kernel
$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', (bool) ($_ENV['APP_DEBUG'] ?? true));
$kernel->boot();

// Get entity manager
$container = $kernel->getContainer();
/** @var EntityManagerInterface $em */
$em = $container->get('doctrine.orm.entity_manager');

echo "Creating test data...\n";

// Create Inspectors
$inspector1 = new Inspector();
$inspector1->setName('John Doe');
$inspector1->setTimezone('Europe/Madrid');
$em->persist($inspector1);

$inspector2 = new Inspector();
$inspector2->setName('Jane Smith');
$inspector2->setTimezone('America/Mexico_City');
$em->persist($inspector2);

$inspector3 = new Inspector();
$inspector3->setName('Bob Johnson');
$inspector3->setTimezone('Europe/London');
$em->persist($inspector3);

// Create Jobs
$job1 = new Job();
$job1->setTitle('Inspection at Site A');
$job1->setStatus(JobStatus::OPEN);
$em->persist($job1);

$job2 = new Job();
$job2->setTitle('Inspection at Site B');
$job2->setStatus(JobStatus::OPEN);
$em->persist($job2);

$job3 = new Job();
$job3->setTitle('Inspection at Site C');
$job3->setStatus(JobStatus::OPEN);
$em->persist($job3);

// Save everything
$em->flush();

echo "✓ Created 3 inspectors:\n";
echo "  - Inspector #{$inspector1->getId()}: {$inspector1->getName()} ({$inspector1->getTimezone()})\n";
echo "  - Inspector #{$inspector2->getId()}: {$inspector2->getName()} ({$inspector2->getTimezone()})\n";
echo "  - Inspector #{$inspector3->getId()}: {$inspector3->getName()} ({$inspector3->getTimezone()})\n";

echo "\n✓ Created 3 jobs:\n";
echo "  - Job #{$job1->getId()}: {$job1->getTitle()}\n";
echo "  - Job #{$job2->getId()}: {$job2->getTitle()}\n";
echo "  - Job #{$job3->getId()}: {$job3->getTitle()}\n";

echo "\nTest data created successfully!\n";
echo "You can now test the API endpoints.\n";
