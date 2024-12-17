<div class="comparison-container">
    <h2>Vergleich der ausgewählten Autos</h2>
    <div class="comparison-table">
        <table>
            <thead>
                <tr>
                    <th>Eigenschaften</th>
                    <?php foreach ($selectedCars as $car): ?>
                        <th>
                            <div class="car-header">
                                <img src="<?php echo esc_url($car['image']); ?>" alt="<?php echo esc_attr($car['name']); ?>">
                                <p><?php echo esc_html($car['name']); ?></p>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Baujahr</td>
                    <?php foreach ($selectedCars as $car): ?>
                        <td><?php echo esc_html($car['year']); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Kraftstoff</td>
                    <?php foreach ($selectedCars as $car): ?>
                        <td><?php echo esc_html($car['fuel']); ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Kilometerstand</td>
                    <?php foreach ($selectedCars as $car): ?>
                        <td><?php echo number_format($car['mileage']); ?> Km</td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Leistung</td>
                    <?php foreach ($selectedCars as $car): ?>
                        <td><?php echo esc_html($car['power']); ?> kW</td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Preis</td>
                    <?php foreach ($selectedCars as $car): ?>
                        <td class="highlight-price"><?php echo number_format($car['price'], 2); ?> €</td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>