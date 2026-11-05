<?php

namespace App\Helpers;

use PDO;

/**
 * Generates unique sequential invoice numbers in the format:
 *   FCDF-MMM-YYYY-0000
 * The sequence resets to 0001 at the start of each calendar month and is
 * generated inside a DB transaction with a locking read (SELECT ... FOR
 * UPDATE) on invoice_counters to prevent duplicates under concurrent
 * submissions.
 */
class InvoiceNumberGenerator
{
    public static function generate(PDO $pdo, ?\DateTime $forDate = null): string
    {
        $forDate = $forDate ?: new \DateTime();
        $yearMonth = $forDate->format('Y-m');
        $monthAbbr = strtoupper($forDate->format('M'));
        $year = $forDate->format('Y');

        $startedHere = !$pdo->inTransaction();
        if ($startedHere) {
            $pdo->beginTransaction();
        }

        try {
            $stmt = $pdo->prepare('SELECT last_seq FROM invoice_counters WHERE period_ym = :ym FOR UPDATE');
            $stmt->execute(['ym' => $yearMonth]);
            $row = $stmt->fetch();

            if ($row === false) {
                $seq = 1;
                $ins = $pdo->prepare('INSERT INTO invoice_counters (period_ym, last_seq) VALUES (:ym, :seq)');
                $ins->execute(['ym' => $yearMonth, 'seq' => $seq]);
            } else {
                $seq = (int) $row['last_seq'] + 1;
                $upd = $pdo->prepare('UPDATE invoice_counters SET last_seq = :seq WHERE period_ym = :ym');
                $upd->execute(['seq' => $seq, 'ym' => $yearMonth]);
            }

            if ($startedHere) {
                $pdo->commit();
            }
        } catch (\Throwable $e) {
            if ($startedHere && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        $seqPadded = str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        return "FCDF-{$monthAbbr}-{$year}-{$seqPadded}";
    }
}
