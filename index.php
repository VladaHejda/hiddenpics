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

$numbersCount = count($horizontalNumbers) * count($verticalNumbers);

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
<script src="http://code.jquery.com/jquery-3.3.1.min.js"></script>

<script>
	$(function () {
		var state = {};
		var numbersCount = <?php echo $numbersCount; ?>;

		var STATES = {
			UNTOUCHED: 0,
			EMPTY: 1,
			FULL: 2
		};

		var locked = false;
		var lock = function () {
			locked = true;
			setTimeout(function () {
				locked = false;
				if (onUnlocked !== null) {
					onUnlocked();
				}
			}, 4000);
		};
		var onUnlocked = null;
		var saveStateAndLock = function () {
			lock();
			onUnlocked = null;
			saveState();
		};

		var saveStatePostponed = function () {
			if (locked) {
				onUnlocked = saveStateAndLock;
			} else {
				saveStateAndLock();
			}
		};

		var getCellId = function ($cell) {
			return parseInt($cell.prop('id').substr(5), 10);
		};

		var setCellState = function ($cell, id, cellState) {
			if (cellState === STATES.FULL) {
				$cell.removeClass('empty').addClass('full');
			} else if (cellState === STATES.EMPTY) {
				$cell.removeClass('full').addClass('empty');
			} else {
				$cell.removeClass('full empty');
			}

			state[id] = cellState;
		};

		var setCellStateById = function (id, cellState) {
			setCellState($('#cell-' + id), id, cellState);
		};

		$('.colourable').on('click', function () {
			var $cell = $(this);
			var id = getCellId($cell);

			var cellState;
			if (state[id] === STATES.FULL) {
				cellState = STATES.EMPTY;
			} else if (state[id] === STATES.EMPTY) {
				cellState = STATES.UNTOUCHED;
			} else {
				cellState = STATES.FULL;
			}

			setCellState($cell, id, cellState);
			saveStatePostponed();

		}).on('contextmenu', function (event) {
			event.preventDefault();
			var $cell = $(this);
			var id = getCellId($cell);

			var cellState;
			if (state[id] === STATES.FULL) {
				cellState = STATES.UNTOUCHED;
			} else if (state[id] === STATES.EMPTY) {
				cellState = STATES.UNTOUCHED;
			} else {
				cellState = STATES.EMPTY;
			}

			setCellState($cell, id, cellState);
			saveStatePostponed();
		});

		$('.number').on('click', function () {
			var $cell = $(this);
			$cell.toggleClass('completed');
		});

		var stateCookieName = 'state';

		var saveState = function () {
			var date = new Date();
			date.setTime(date.getTime() + (300 * 24 * 60 * 60 * 1000));

			var value = '';
			for (var id = 0; id < numbersCount; id++) {
				value += state[id].toString();
			}

			document.cookie = stateCookieName + '=' + value + '; expires=' + date.toUTCString() + '; path=/';
		};

		var loadState = function () {
			var cookies = document.cookie.split(';');

			var value = null;
			for(var i = 0; i < cookies.length; i++) {
				var cookie = cookies[i];
				while (cookie.charAt(0) === ' ') {
					cookie = cookie.substring(1, cookie.length);
				}
				if (cookie.indexOf(stateCookieName + '=') === 0) {
					value = cookie.substring(stateCookieName.length + 1, cookie.length);
					break;
				}
			}

			for (var id = 0; id < numbersCount; id++) {
				var cellState = STATES.UNTOUCHED;
				if (value !== null) {
					cellState = value.charAt(id);
					cellState = parseInt(cellState, 10);

					if (isNaN(cellState) || (cellState !== STATES.FULL && cellState !== STATES.EMPTY)) {
						cellState = STATES.UNTOUCHED;
					}
				}
				setCellStateById(id, cellState);
			}
		};

		loadState();
	});

	window.onerror = function (m) {
		alert(m);
	};
</script>

<style>
	table {
		border-collapse: collapse;
		width: 100%;
		height: 100%;
	}
	td {
		border-style: solid;
		border-color: black;
		border-width: 1px;
		min-width: 17px;
		min-height: 20px;
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


$counter = 0;
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
		$classes = ['colourable'];
		if ($x % 5 === 0) {
			$classes[] = 'highlight-column';
		}
		echo '<td id="cell-' . $counter . '" class="' . implode(' ', $classes) . '">';

		echo '</td>';
		$counter++;
	}
	echo '</tr>';
}
echo '</table>';
