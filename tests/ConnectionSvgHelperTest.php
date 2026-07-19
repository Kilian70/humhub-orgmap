<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/helpers/ConnectionSvgHelper.php';

use humhub\modules\orgmap\helpers\ConnectionSvgHelper;

function failTest(string $message): void
{
    fwrite(STDERR, "FEHLER: {$message}" . PHP_EOL);
    exit(1);
}

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        failTest($message . ' – erwartet: ' . var_export($expected, true)
            . ', erhalten: ' . var_export($actual, true));
    }
}

function assertNear(float $expected, float $actual, string $message, float $delta = 0.00001): void
{
    if (abs($expected - $actual) > $delta) {
        failTest($message . " – erwartet: {$expected}, erhalten: {$actual}");
    }
}

$geometry = ConnectionSvgHelper::buildConnectionGeometry(0, 0, 100, 0, 20);
if ($geometry === null) {
    failTest('Geometrie für unterschiedliche Punkte darf nicht leer sein.');
}

assertNear(100.0, (float) $geometry['distance'], 'Distanz wird korrekt berechnet.');
assertNear(50.0, (float) $geometry['midX'], 'Horizontaler Mittelpunkt ist korrekt.');
assertNear(0.0, (float) $geometry['midY'], 'Vertikaler Mittelpunkt ist korrekt.');
assertNear(50.0, (float) $geometry['controlX'], 'Kontrollpunkt X ist korrekt.');
assertNear(20.0, (float) $geometry['controlY'], 'Kurve verschiebt den Kontrollpunkt.');

assertSameValue(
    null,
    ConnectionSvgHelper::buildConnectionGeometry(10, 10, 10, 10, 0),
    'Identische Punkte erzeugen keine Verbindung.'
);

$edges = ConnectionSvgHelper::buildEdgePoints(0, 0, 10, 100, 0, 20);
if ($edges === null) {
    failTest('Randpunkte für unterschiedliche Knoten dürfen nicht leer sein.');
}

assertNear(10.0, (float) $edges['x1'], 'Linie beginnt am Rand des Startknotens.');
assertNear(80.0, (float) $edges['x2'], 'Linie endet am Rand des Zielknotens.');
assertNear(0.0, (float) $edges['y1'], 'Startpunkt bleibt auf der horizontalen Achse.');
assertNear(0.0, (float) $edges['y2'], 'Endpunkt bleibt auf der horizontalen Achse.');

$label = ConnectionSvgHelper::buildLabelPosition($geometry);
assertNear(50.0, (float) $label['x'], 'Automatische Labelposition X ist korrekt.');
assertNear(10.0, (float) $label['y'], 'Automatische Labelposition Y ist korrekt.');

assertSameValue(
    ['x' => 12, 'y' => -4],
    ConnectionSvgHelper::buildLabelPosition($geometry, 12, -4),
    'Manuelle Labelposition wird übernommen.'
);

assertSameValue('8 4', ConnectionSvgHelper::buildStrokeDasharray('dashed'), 'Gestrichelte Linie.');
assertSameValue('2 6', ConnectionSvgHelper::buildStrokeDasharray('dotted'), 'Gepunktete Linie.');
assertSameValue(null, ConnectionSvgHelper::buildStrokeDasharray('solid'), 'Durchgezogene Linie.');

assertSameValue(true, ConnectionSvgHelper::hasStartArrow('start'), 'Startpfeil wird erkannt.');
assertSameValue(true, ConnectionSvgHelper::hasStartArrow('both'), 'Beidseitiger Startpfeil wird erkannt.');
assertSameValue(false, ConnectionSvgHelper::hasStartArrow('end'), 'Endpfeil ist kein Startpfeil.');
assertSameValue(true, ConnectionSvgHelper::hasEndArrow('end'), 'Endpfeil wird erkannt.');
assertSameValue(true, ConnectionSvgHelper::hasEndArrow('both'), 'Beidseitiger Endpfeil wird erkannt.');

$path = ConnectionSvgHelper::buildPath($geometry, 0, 0, 100, 0);
if (!str_contains($path, 'M 0 0') || !str_contains($path, 'Q 50 20') || !str_contains($path, '100 0')) {
    failTest('SVG-Pfad enthält nicht die erwarteten Punkte.');
}

assertSameValue('', ConnectionSvgHelper::buildPath($geometry, 5, 5, 5, 5), 'Nullpfad bleibt leer.');

echo "ConnectionSvgHelper: alle Tests erfolgreich" . PHP_EOL;
