<?php
namespace App\Database\Seeders;

use WebFiori\Database\Database;
use WebFiori\Database\Schema\AbstractMigration;

class SeedSampleData extends AbstractMigration {
    public function down(Database $db): void {
    }

    public function up(Database $db): void {
        $users = [
            ['Admin', 'admin@clinic.com', '+1000000000', password_hash('admin123', PASSWORD_BCRYPT), 'admin'],
            ['Dr. Smith', 'smith@clinic.com', '+1000000001', password_hash('smith123', PASSWORD_BCRYPT), 'provider'],
            ['Dr. Jones', 'jones@clinic.com', '+1000000002', password_hash('jones123', PASSWORD_BCRYPT), 'provider'],
            ['Alice Patient', 'alice@example.com', '+1555000001', password_hash('alice123', PASSWORD_BCRYPT), 'patient'],
            ['Bob Patient', 'bob@example.com', '+1555000002', password_hash('bob123', PASSWORD_BCRYPT), 'patient'],
        ];

        foreach ($users as [$name, $email, $phone, $hash, $role]) {
            $db->table('users')->insert([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password-hash' => $hash,
                'role' => $role,
                'active' => true,
            ])->execute();
        }

        $services = [
            ['General Consultation', 30, 50.00],
            ['Dental Cleaning', 45, 80.00],
            ['Physical Therapy', 60, 120.00],
            ['Eye Exam', 20, 40.00],
        ];

        foreach ($services as [$name, $duration, $price]) {
            $db->table('services')->insert([
                'name' => $name,
                'duration-minutes' => $duration,
                'price' => $price,
            ])->execute();
        }

        // Sample appointments
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $db->table('appointments')->insert([
            'patient-id' => 4,
            'provider-id' => 2,
            'service-id' => 1,
            'start-time' => $tomorrow . ' 09:00:00',
            'end-time' => $tomorrow . ' 09:30:00',
            'status' => 'booked',
            'reminder-sent' => false,
            'created-at' => date('Y-m-d H:i:s'),
        ])->execute();
    }
}
