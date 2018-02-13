<?php

$filename = '001.png';

function calculateNumbers($filename, $reverse = false)
{
	list ($maxX, $maxY) = getimagesize($filename);
	$resource = imagecreatefrompng($filename);

	if ($reverse) {
		list ($maxX, $maxY) = [$maxY, $maxX];
	}

	$numbers = [];
	for ($y = 0; $y < $maxY; $y++) {
		$isPreviousFilled = false;
		$filledCount = 0;
		$isFilled = false;
		$numbers[$y] = [];

		for ($x = 0; $x < $maxX; $x++) {
			$coordinates = $reverse ? [$y, $x] : [$x, $y];

			$color = imagecolorat($resource, ...$coordinates);
			$isFilled = $color < 8388607;

			if ($isFilled) {
				if ($isPreviousFilled) {
					$filledCount++;
				} else {
					$filledCount = 1;
				}
				$isPreviousFilled = $isFilled;
			} elseif ($filledCount > 0) {
				$numbers[$y][] = $filledCount;
				$filledCount = 0;
			}
		}

		if ($isFilled) {
			$numbers[$y][] = $filledCount;
		}
	}

	return $numbers;
}

$horizontalNumbers = calculateNumbers($filename);
$verticalNumbers = calculateNumbers($filename, true);

function findMaxCount(array $numbers)
{
	$max = 0;
	foreach ($numbers as $number) {
		$max = max($max, count($number));
	}

	return $max;
}

$horizontalMaxCount = findMaxCount($horizontalNumbers);
$verticalMaxCount = findMaxCount($verticalNumbers);

?>
<script src="http://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

<script>
	$(function () {
		$('.colourable').on('click', function () {
			var $cell = $(this);
			if ($cell.hasClass('full')) {
				$cell.removeClass('full').addClass('empty');
			} else if ($cell.hasClass('empty')) {
				$cell.removeClass('empty');
			} else {
				$cell.addClass('full');
			}
		}).on('contextmenu', function (event) {
			event.preventDefault();
			var $cell = $(this);
			if ($cell.hasClass('full')) {
				$cell.removeClass('full');
			} else {
				$cell.toggleClass('empty');
			}
		});

		$('.number').on('click', function () {
			var $cell = $(this);
			$cell.toggleClass('completed');
		});

		$(window).on('beforeunload', function (event) {
			return true;
		});
	});
</script>

<style>
	table {
		border-collapse: collapse;
	}
	td {
		border-style: solid;
		border-color: black;
		border-width: 1px;
		width: 17px;
		height: 20px;
		text-align: center;
	}
	.numbers-horizontal {
		border-left-color: lightgray;
		border-right-color: lightgray;
	}
	.numbers-vertical {
		border-top-color: lightgray;
		border-bottom-color: lightgray;
	}
	.highlight-row td {
		border-top-color: black !important;
		border-top-width: 2px;
	}
	.highlight-column {
		border-left-color: black !important;
		border-left-width: 2px;
	}
	.empty {
		background-color: lightgray;
	}
	.full {
		background-color: black;
	}
	.number {
		cursor: default;
	}
	.completed {
		text-decoration: line-through;
		color: lightslategrey;
	}
</style>
<?php

echo '<table>';
for ($y = 0; $y < $verticalMaxCount; $y++) {
	echo '<tr>';

	for ($x = 0; $x < $horizontalMaxCount; $x++) {
		echo '<td class="numbers-vertical numbers-horizontal"></td>';
	}

	foreach ($verticalNumbers as $x => $numbers) {
		$index = $y + count($numbers) - $verticalMaxCount;

		$classes = ['numbers-vertical'];
		if ($x % 5 === 0) {
			$classes[] = 'highlight-column';
		}
		if ($index >= 0) {
			$classes[] = 'number';
		}

		echo '<td class="' . implode(' ', $classes) . '">';

		if ($index >= 0) {
			echo $numbers[$index];
		}
		echo '</td>';
	}
	echo '</tr>';
}


foreach (array_keys($horizontalNumbers) as $y) {
	if ($y % 5 === 0) {
		echo '<tr class="highlight-row">';
	} else {
		echo '<tr>';
	}

	for ($x = 0; $x < $horizontalMaxCount; $x++) {
		$index = $x + count($horizontalNumbers[$y]) - $horizontalMaxCount;

		$classes = ['numbers-horizontal'];
		if ($index >= 0) {
			$classes[] = 'number';
		}

		echo '<td class="' . implode(' ', $classes) . '">';

		if ($index >= 0) {
			echo $horizontalNumbers[$y][$index];
		}
		echo '</td>';
	}

	foreach (array_keys($verticalNumbers) as $x) {
		if ($x % 5 === 0) {
			echo '<td class="colourable highlight-column">';
		} else {
			echo '<td class="colourable">';
		}
		echo '</td>';
	}
	echo '</tr>';
}
echo '</table>';
