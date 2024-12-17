document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('filter-sidebar');
    const gridContainer = document.querySelector('.grid-container');
    const form = document.getElementById('car-filter-form');
  

    // Compare Checkbox Logic
    const compareCheckboxes = document.querySelectorAll('.compare-check');
    const compareButton = document.getElementById('compare-selected-cars');
    let selectedCarIds = [];

    compareCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const carId = this.dataset.carId;

            if (this.checked) {
                if (selectedCarIds.length < 3) {
                    selectedCarIds.push(carId);
                } else {
                    alert("Sie können maximal 3 Autos vergleichen.");
                    this.checked = false;
                }
            } else {
                selectedCarIds = selectedCarIds.filter(id => id !== carId);
            }

            compareButton.disabled = selectedCarIds.length === 0;
            console.log("Selected Cars:", selectedCarIds);
        });
    });

    // Comparison Popup Logic
    const comparisonPopup = document.getElementById('comparison-popup');
    const closePopupButton = document.getElementById('close-comparison-popup');
    const tableHeader = document.getElementById('table-header');
    const tableRows = document.querySelectorAll('#comparison-table tbody tr');

    function populateComparisonTable(cars) {
        tableHeader.innerHTML = '<th class="comparison-attribute-header">Eigenschaften</th>';
        const labels = ["Baujahr", "Kraftstoff", "Kilometerstand", "Leistung", "Preis"];
        tableRows.forEach((row, index) => {
            row.innerHTML = `<td class="comparison-attribute-label">${labels[index]}</td>`;
        });

        cars.forEach(car => {
            const headerCell = document.createElement('th');
            headerCell.innerHTML = `
                <div class="car-header">
                    <img src="${car.image_thumbnail}" alt="${car.car_name}">
                    <p>${car.car_name}</p>
                </div>
            `;
            tableHeader.appendChild(headerCell);

            tableRows[0].insertAdjacentHTML('beforeend', `<td>${car.car_year || 'N/A'}</td>`);
            tableRows[1].insertAdjacentHTML('beforeend', `<td>${car.fuel_type || 'N/A'}</td>`);
            tableRows[2].insertAdjacentHTML('beforeend', `<td>${car.mileage_km || 'N/A'} km</td>`);
            tableRows[3].insertAdjacentHTML('beforeend', `<td>${car.power_kw || 'N/A'} kW</td>`);
            tableRows[4].insertAdjacentHTML('beforeend', `<td>${car.price || 'N/A'} €</td>`);
        });
    }

    compareButton.addEventListener('click', function (event) {
        event.preventDefault();
        const selectedCars = document.querySelectorAll('.compare-check:checked');
        const carIds = Array.from(selectedCars).map(checkbox => checkbox.dataset.carId);

        if (carIds.length === 0) {
            alert("Bitte wählen Sie mindestens ein Auto zum Vergleichen aus.");
            return;
        }

        fetch(ajaxurl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=fetch_car_data&car_ids=${encodeURIComponent(carIds.join(','))}`
        })
        .then(response => response.json())
        .then(data => {
            populateComparisonTable(data);
            comparisonPopup.classList.remove('hidden');
            console.log("Popup geöffnet");
        })
        .catch(error => {
            console.error("Error fetching car data:", error);
            alert("Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.");
        });
    });

    closePopupButton.addEventListener('click', function () {
        comparisonPopup.classList.add('hidden');
        console.log("Popup geschlossen");
    });

    comparisonPopup.addEventListener('click', function (event) {
        if (!event.target.closest('.comparison-popup-content')) {
            comparisonPopup.classList.add('hidden');
            console.log("Popup geschlossen durch Klick außerhalb.");
        }
    });

    // Range Slider Logic with LocalStorage
    const ranges = [
        { min: 'price-min', max: 'price-max', minValue: 'price-min-value', maxValue: 'price-max-value' },
        { min: 'year-min', max: 'year-max', minValue: 'year-min-value', maxValue: 'year-max-value' },
        { min: 'power-min', max: 'power-max', minValue: 'power-min-value', maxValue: 'power-max-value' },
        { min: 'tuv-min', max: 'tuv-max', minValue: 'tuv-min-value', maxValue: 'tuv-max-value' }
    ];

    function updateSliderTrack(minInput, maxInput) {
        const min = parseFloat(minInput.min);
        const max = parseFloat(minInput.max);
        const minVal = parseFloat(minInput.value);
        const maxVal = parseFloat(maxInput.value);
    
        const minPercent = ((minVal - min) / (max - min)) * 100;
        const maxPercent = ((maxVal - min) / (max - min)) * 100;
    
        // Using white for outer parts and red for the selected range
        minInput.style.background = `linear-gradient(to right, 
            rgba(255, 255, 255, 0.133) 0%, 
            rgba(255, 255, 255, 0.133) ${minPercent}%, 
            rgba(255, 255, 255) ${minPercent}%, 
            rgba(255, 255, 255) ${maxPercent}%, 
            rgba(255, 255, 255, 0.133) ${maxPercent}%, 
            rgba(255, 255, 255, 0.133) 100%) center/100% 1px no-repeat`;
    
        maxInput.style.background = 'transparent';
        
        minInput.style.zIndex = '1';
        maxInput.style.zIndex = '2';
    }
    
    function saveFiltersToLocalStorage() {
        ranges.forEach(range => {
            const minInput = document.getElementById(range.min);
            const maxInput = document.getElementById(range.max);
            localStorage.setItem(range.min, minInput.value);
            localStorage.setItem(range.max, maxInput.value);
        });
    }

    // Add this helper function at the top of your file
function formatPrice(price) {
    if (price >= 1000) {
        return Math.round(price/1000) + 'k';
    }
    return price;
}

// Modify your loadFiltersFromLocalStorage function
function loadFiltersFromLocalStorage() {
    ranges.forEach(range => {
        const minInput = document.getElementById(range.min);
        const maxInput = document.getElementById(range.max);
        const minValue = document.getElementById(range.minValue);
        const maxValue = document.getElementById(range.maxValue);

        const savedMin = localStorage.getItem(range.min);
        const savedMax = localStorage.getItem(range.max);

        if (savedMin && savedMax) {
            minInput.value = savedMin;
            maxInput.value = savedMax;
            // Format price values differently
            if (range.min === 'price-min') {
                minValue.textContent = formatPrice(savedMin);
                maxValue.textContent = formatPrice(savedMax);
            } else {
                minValue.textContent = savedMin;
                maxValue.textContent = savedMax;
            }
            updateSliderTrack(minInput, maxInput);
        }

        minInput.addEventListener('input', () => {
            // Format price values differently
            if (range.min === 'price-min') {
                minValue.textContent = formatPrice(minInput.value);
            } else {
                minValue.textContent = minInput.value;
            }
            updateSliderTrack(minInput, maxInput);
            saveFiltersToLocalStorage();
        });

        maxInput.addEventListener('input', () => {
            // Format price values differently
            if (range.min === 'price-min') {
                maxValue.textContent = formatPrice(maxInput.value);
            } else {
                maxValue.textContent = maxInput.value;
            }
            updateSliderTrack(minInput, maxInput);
            saveFiltersToLocalStorage();
        });

        // Initial track update
        updateSliderTrack(minInput, maxInput);
    });
}

    // Load saved filter values on page load
    loadFiltersFromLocalStorage();

    // Reset Button
    const resetButton = document.getElementById('filter-reset');
    resetButton.addEventListener('click', function () {
        document.getElementById('car-filter-form').reset();
        localStorage.clear(); // Clear saved filters
        ranges.forEach(range => {
            const minInput = document.getElementById(range.min);
            const maxInput = document.getElementById(range.max);
            minInput.dispatchEvent(new Event('input'));
            maxInput.dispatchEvent(new Event('input'));
            updateSliderTrack(minInput, maxInput);
        });
    });

    // Save filters on submit
   // Modify your form submit handler
const filterForm = document.getElementById('car-filter-form');
filterForm.addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission
    
    // Get all filter values
    const filterData = new FormData(filterForm);
    let queryParams = new URLSearchParams();

    // Add range filter values
    ranges.forEach(range => {
        const minInput = document.getElementById(range.min);
        const maxInput = document.getElementById(range.max);
        queryParams.append(range.min, minInput.value);
        queryParams.append(range.max, maxInput.value);
    });

    // Add checkbox values (fuel type and car type)
    const fuelTypes = document.querySelectorAll('input[name="fuel_type[]"]:checked');
    fuelTypes.forEach(checkbox => {
        queryParams.append('fuel_type[]', checkbox.value);
    });

    const carTypes = document.querySelectorAll('input[name="car_type[]"]:checked');
    carTypes.forEach(checkbox => {
        queryParams.append('car_type[]', checkbox.value);
    });

    // Save to localStorage
    saveFiltersToLocalStorage();

    // Redirect with query parameters
    window.location.href = `${window.location.pathname}?${queryParams.toString()}`;
});
});


// apply filter 
function applyFiltersFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Apply range filters
    ranges.forEach(range => {
        const minInput = document.getElementById(range.min);
        const maxInput = document.getElementById(range.max);
        const minValue = document.getElementById(range.minValue);
        const maxValue = document.getElementById(range.maxValue);

        if (urlParams.has(range.min)) {
            minInput.value = urlParams.get(range.min);
            minValue.textContent = range.min === 'price-min' ? 
                formatPrice(urlParams.get(range.min)) : 
                urlParams.get(range.min);
        }

        if (urlParams.has(range.max)) {
            maxInput.value = urlParams.get(range.max);
            maxValue.textContent = range.min === 'price-min' ? 
                formatPrice(urlParams.get(range.max)) : 
                urlParams.get(range.max);
        }

        updateSliderTrack(minInput, maxInput);
    });

    // Apply checkbox filters
    const fuelTypes = urlParams.getAll('fuel_type[]');
    fuelTypes.forEach(type => {
        const checkbox = document.querySelector(`input[name="fuel_type[]"][value="${type}"]`);
        if (checkbox) checkbox.checked = true;
    });

    const carTypes = urlParams.getAll('car_type[]');
    carTypes.forEach(type => {
        const checkbox = document.querySelector(`input[name="car_type[]"][value="${type}"]`);
        if (checkbox) checkbox.checked = true;
    });
}

// Call this function after DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    // ... your existing DOMContentLoaded code ...
    
    loadFiltersFromLocalStorage();
    applyFiltersFromURL(); // Apply filters from URL parameters
});



 