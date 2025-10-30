<?php

namespace App\Services;

use App\Entity\House;
use App\Entity\Booking;

class ServicesCSV {
     private string $projectDir;

    public function __construct(string $projectDir) {
        $this->projectDir = $projectDir;
    }

    public function readCsv(string $filename): array {
        $filepath = $this->projectDir . '/data/' . $filename;
        $data = [];
        
        if (!file_exists($filepath)) {
            return $data;
        }
        
        if ($handle = fopen($filepath, 'r')) {
            $headers = fgetcsv($handle);
            
            while ($rowData = fgetcsv($handle)) {
                if (!empty($rowData) && count($rowData) === count($headers)) {
                    $data[] = array_combine($headers, $rowData);
                }
            }
            fclose($handle);
        }
        
        return $data;
    }

    public function writeCsv(string $filename, array $data): bool {
        $filepath = $this->projectDir . '/data/' . $filename;
        $fileExists = file_exists($filepath);
        
        $handle = fopen($filepath, 'a');
        
        if (!$fileExists) {
            fputcsv($handle, array_keys($data));
        }
        
        $result = fputcsv($handle, $data);
        fclose($handle);
        
        return $result;
    }

    public function updateCsv(string $filename, array $newData, callable $findCallback): bool
    {
        $filepath = $this->projectDir . '/data/' . $filename;
        
        if (!file_exists($filepath)) {
            return false;
        }
        
        $allData = $this->readCsv($filename);
        $updated = false;
        
        foreach ($allData as &$item) {
            if ($findCallback($item)) {
                $item = array_merge($item, $newData);
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $handle = fopen($filepath, 'w');
            if (!empty($allData)) {
                fputcsv($handle, array_keys($allData[0]));
                foreach ($allData as $row) {
                    fputcsv($handle, $row);
                }
            } else {
                fputcsv($handle, ['id', 'house_id', 'phone', 'comment', 'created_at', 'status']);
            }
            fclose($handle);
            return true;
        }
        
        return false;
    }

    public function getAvailableHouses(): array {
        $housesData = $this->readCsv('houses.csv');
        $houses = [];
        
        foreach ($housesData as $houseData) {
            if ($houseData['is_available'] === '1') {
                $houses[] = new House(
                    (int)$houseData['id'],
                    $houseData['name'],
                    (int)$houseData['beds'],
                    $houseData['amenities'],
                    (int)$houseData['distance_to_sea'],
                    (float)$houseData['price_per_night']
                );
            }
        }
        
        return $houses;
    }

    public function getHouseById(int $houseId): ?House {
        $houses = $this->getAvailableHouses();
        foreach ($houses as $house) {
            if ($house->id === $houseId) {
                return $house;
            }
        }
        return null;
    }


    public function createBooking(int $houseId, string $phone, string $comment): bool {
        $bookingsData = $this->readCsv('bookings.csv');
        
        $maxId = 0;
        foreach ($bookingsData as $booking) {
            $currentId = (int)$booking['id'];
            if ($currentId > $maxId) {
                $maxId = $currentId;
            }
        }
        
        $newId = $maxId + 1;
        
        $booking = new Booking(
            $newId,
            $houseId,
            $phone,
            $comment,
            date('Y-m-d H:i:s')
        );
        
        return $this->writeCsv('bookings.csv', $booking->toArray());
    }

    public function updateBooking(int $bookingId, string $newComment): bool {
        return $this->updateCsv('bookings.csv', 
            ['comment' => $newComment],
            function($booking) use ($bookingId) {
                return (int)$booking['id'] === $bookingId;
            }
        );
    }

    public function deleteBooking(int $bookingId): bool {
        $bookingsData = $this->readCsv('bookings.csv');
        
        $filteredBookings = array_filter($bookingsData, function($booking) use ($bookingId) {
            return (int)$booking['id'] !== $bookingId;
        });
        
        if (count($filteredBookings) < count($bookingsData)) {
            $filepath = $this->projectDir . '/data/bookings.csv';
            $handle = fopen($filepath, 'w');
            
            fputcsv($handle, ['id', 'house_id', 'phone', 'comment', 'created_at', 'status']);
            
            foreach ($filteredBookings as $row) {
                fputcsv($handle, $row);
            }
            
            fclose($handle);
            return true;
        }
        
        return false;
    }

    public function getBookingById(int $bookingId): ?Booking {
        $bookingsData = $this->readCsv('bookings.csv');
        foreach ($bookingsData as $bookingData) {
            if ((int)$bookingData['id'] === $bookingId) {
                return new Booking(
                    (int)$bookingData['id'],
                    (int)$bookingData['house_id'],
                    $bookingData['phone'],
                    $bookingData['comment'],
                    $bookingData['created_at']
                );
            }
        }
        return null;
    }
}
