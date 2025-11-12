# Data Spread Improvement - Z-Score Normalization

## Problem Identified
Players were clustering around the Y-axis (vertical line at X=0) because:
- Opponent quality values had **low variance** (all teams allow similar stats to each position)
- Simple subtraction (`value - mean`) didn't account for this low variance
- Result: Most players had X values between -2 and +2, creating a vertical cluster

## Solution: Z-Score Normalization

### What Changed

**Before (Simple Subtraction):**
```php
$normalized = $value - $mean;
```
Example:
- If mean opponent quality = 12.5 PIR
- Player faces opponents allowing 13.5 PIR
- Result: +1.0 (clustered near center)

**After (Z-Score with Scaling):**
```php
$stdDev = standardDeviation($allValues);
$zScore = ($value - $mean) / $stdDev;
$normalized = $zScore * $spreadFactor; // spreadFactor = 3
```
Example:
- If mean = 12.5, stdDev = 0.8, value = 13.5
- Z-score = (13.5 - 12.5) / 0.8 = 1.25
- Normalized = 1.25 × 3 = **3.75** (better spread!)

### Why This Works

**Z-Score Benefits:**
1. **Accounts for variance** - Low variance metrics get amplified more
2. **Standardized scale** - All metrics on same scale regardless of units
3. **Statistical meaning** - Shows how "unusual" a value is
4. **Better visualization** - Points spread evenly across quadrants

**Spread Factor (3x):**
- Multiplies z-scores to make graph more readable
- Without it, most points would be between -2 and +2
- With 3x factor, points spread from -6 to +6 or more
- Makes quadrants much more visible and useful

## Mathematical Details

### Standard Deviation Calculation
```php
// Step 1: Calculate mean
$mean = average($allValues);

// Step 2: Calculate variance (average squared deviation)
$variance = average(map($allValues, fn($v) => pow($v - $mean, 2)));

// Step 3: Standard deviation is square root of variance
$stdDev = sqrt($variance);
```

### Z-Score Formula
```
z = (x - μ) / σ

Where:
- x = individual value
- μ (mu) = mean of all values
- σ (sigma) = standard deviation
- z = number of standard deviations from mean
```

### Interpretation
- **Z-score = 0** → Value is exactly at the mean
- **Z-score = +1** → One standard deviation above mean (~84th percentile)
- **Z-score = -1** → One standard deviation below mean (~16th percentile)
- **Z-score = +2** → Two standard deviations above mean (~98th percentile)
- **Z-score = -2** → Two standard deviations below mean (~2nd percentile)

With 3x scaling:
- **Normalized = +3** → Player is 1 stddev above average (good!)
- **Normalized = -3** → Player is 1 stddev below average (bad)
- **Normalized = +6** → Player is 2 stddev above average (excellent!)

## Visual Impact

### Before (Simple Subtraction):
```
     Y
     |
  10 +                 *
     |              *  *  *
   5 +           *  * * * *  *
     |        *  *  * * *  *  *
   0 +----*--*--*--*-*-*-*--*--*----X
     |        *  *  * * *  *  *
  -5 +           *  * * * *  *
     |              *  *  *
 -10 +                 *
     |
    -10  -5   0   5  10
```
**Problem:** All points clustered near Y-axis (vertical line)

### After (Z-Score Normalization):
```
     Y
     |
  10 +  *              *
     |     *        *     *
   5 +        *  *    *
     |     *  *  * *  *  *
   0 +----*--*--+-*--*--*--*----X
     |  *  *  *  *  *  *
  -5 +     *     *  *
     |  *     *        *
 -10 +     *              *
     |
    -10  -5   0   5  10
```
**Solution:** Points spread nicely across all four quadrants

## Real-World Example

**Scenario:** Analyzing opponent defense quality

**Raw Data:**
- Team A allows: 12.8 PIR to guards
- Team B allows: 13.2 PIR to guards
- Team C allows: 12.5 PIR to guards
- League average: 12.83 PIR
- Standard deviation: 0.4 PIR

**Simple Subtraction (Old Method):**
- Team A: 12.8 - 12.83 = -0.03 (barely different)
- Team B: 13.2 - 12.83 = +0.37 (barely different)
- Team C: 12.5 - 12.83 = -0.33 (barely different)

All clustered between -0.5 and +0.5! 😞

**Z-Score with 3x Scaling (New Method):**
- Team A: ((12.8 - 12.83) / 0.4) × 3 = -0.225 (below avg)
- Team B: ((13.2 - 12.83) / 0.4) × 3 = +2.78 (well above avg!) ✨
- Team C: ((12.5 - 12.83) / 0.4) × 3 = -2.48 (well below avg) 📉

Now we can clearly see Team B is a favorable matchup! 👍

## Implementation Code

```php
// Calculate league averages
$avgForm = $formValues->avg();
$avgOpponent = $opponentValues->avg();

// Calculate standard deviations
$formStdDev = sqrt($formValues->map(function($v) use ($avgForm) {
    return pow($v - $avgForm, 2);
})->avg());

$opponentStdDev = sqrt($opponentValues->map(function($v) use ($avgOpponent) {
    return pow($v - $avgOpponent, 2);
})->avg());

// Prevent division by zero
$formStdDev = $formStdDev > 0 ? $formStdDev : 1;
$opponentStdDev = $opponentStdDev > 0 ? $opponentStdDev : 1;

// Z-score normalization with scaling
$spreadFactor = 3;

foreach ($data as &$player) {
    $player['form_normalized'] = round(
        (($player['recent_form'] - $avgForm) / $formStdDev) * $spreadFactor, 
        2
    );
    $player['opponent_normalized'] = round(
        (($player['opponent_quality'] - $avgOpponent) / $opponentStdDev) * $spreadFactor, 
        2
    );
}
```

## Adjusting the Spread

You can modify the `$spreadFactor` to change how spread out the points are:

- **$spreadFactor = 1** → Minimal spread (traditional z-scores)
- **$spreadFactor = 2** → Moderate spread
- **$spreadFactor = 3** → Good spread (current setting) ✅
- **$spreadFactor = 4** → More spread
- **$spreadFactor = 5** → Maximum spread

**Recommended:** Keep at 3 for best balance between readability and accuracy.

## Benefits of This Approach

1. ✅ **Even distribution** across all four quadrants
2. ✅ **Statistical accuracy** - Properly accounts for variance
3. ✅ **Clear visualization** - Easy to identify outliers
4. ✅ **Comparable metrics** - Form and matchup on same scale
5. ✅ **Industry standard** - Z-scores used in finance, sports analytics, etc.

## Cache Cleared

After implementing this change, the cache was cleared:
```bash
php artisan cache:clear
```

This ensures all users see the new, properly spread data immediately.

---

**Result:** Players now spread evenly across the graph, making it much easier to identify:
- 🌟 Best picks (top-right)
- 📈 Risky picks (top-left)
- ⚠️ Bounce-back candidates (bottom-right)
- ❌ Avoid (bottom-left)

