/* ==========================================================================
   Practical 6: Rendering External JSON Data using Fetch API, Search & Filter
   CHAROTAR UNIVERSITY OF SCIENCE AND TECHNOLOGY (CHARUSAT)
   Department of Information Technology
   Vanilla JavaScript ES6+ (No Frameworks)
   ========================================================================== */

(function () {
    "use strict";

    // --- State Variables ---
    let currentTab = "events"; // "events" | "students" | "faqs"
    let rawData = [];
    let filteredData = [];
    let currentPage = 1;
    let pageSize = 6;
    let isOfflineCached = false;

    // --- DOM Elements ---
    const tabEvents = document.getElementById("tabEvents");
    const tabStudents = document.getElementById("tabStudents");
    const tabFaqs = document.getElementById("tabFaqs");

    const dataSourceBadge = document.getElementById("dataSourceBadge");
    const recordCountText = document.getElementById("recordCountText");
    const forceRefreshBtn = document.getElementById("forceRefreshBtn");
    const clearCacheBtn = document.getElementById("clearCacheBtn");

    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const categoryFilterLabel = document.getElementById("categoryFilterLabel");
    const sortFilter = document.getElementById("sortFilter");
    const pageSizeSelect = document.getElementById("pageSizeSelect");
    const resetFiltersBtn = document.getElementById("resetFiltersBtn");

    // Dependent Dropdowns (Intermediate Extension)
    const dependentDropdownsSection = document.getElementById("dependentDropdownsSection");
    const countrySelect = document.getElementById("countrySelect");
    const stateSelect = document.getElementById("stateSelect");
    const citySelect = document.getElementById("citySelect");

    // States & Containers
    const loadingBox = document.getElementById("loadingBox");
    const errorBox = document.getElementById("errorBox");
    const errorMessage = document.getElementById("errorMessage");
    const retryFetchBtn = document.getElementById("retryFetchBtn");
    const emptyBox = document.getElementById("emptyBox");
    const dataGrid = document.getElementById("dataGrid");
    const faqList = document.getElementById("faqList");

    // Pagination
    const paginationContainer = document.getElementById("paginationContainer");
    const paginationInfo = document.getElementById("paginationInfo");
    const paginationButtons = document.getElementById("paginationButtons");

    // Only run if on practical 6 page
    if (!dataGrid) return;


    /* ======================================================================
       1. DATA FETCHING WITH FETCH API & LOCALSTORAGE CACHE (Advanced Extension)
       ====================================================================== */

    const JSON_FILES = {
        events: "../data/events.json",
        students: "../data/students.json",
        faqs: "../data/faqs.json"
    };

    const CACHE_KEYS = {
        events: "studenthub_cached_events_v1",
        students: "studenthub_cached_students_v1",
        faqs: "studenthub_cached_faqs_v1"
    };

    /**
     * Fetches external JSON data using Fetch API with caching & error handling
     */
    async function loadData(forceLive = false) {
        showLoading(true);
        hideError();
        isOfflineCached = false;

        const url = JSON_FILES[currentTab];
        const cacheKey = CACHE_KEYS[currentTab];

        // Advanced Extension: Check localStorage cache first unless forced live
        if (!forceLive) {
            const cachedString = localStorage.getItem(cacheKey);
            if (cachedString) {
                try {
                    const parsed = JSON.parse(cachedString);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        rawData = parsed;
                        isOfflineCached = true;
                        updateStatusBadge(true);
                        populateFilters();
                        applyFiltersAndRender();
                        showLoading(false);
                        return;
                    }
                } catch (e) {
                    console.warn("Invalid cache format, proceeding to fetch fresh JSON.");
                }
            }
        }

        try {
            // Fetch API Request
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} (${response.statusText})`);
            }

            const data = await response.json();

            if (!Array.isArray(data)) {
                throw new Error("Invalid data format: Expected a JSON array.");
            }

            rawData = data;

            // Cache response into localStorage for offline use
            try {
                localStorage.setItem(cacheKey, JSON.stringify(data));
            } catch (storageErr) {
                console.warn("localStorage quota exceeded or disabled.", storageErr);
            }

            isOfflineCached = false;
            updateStatusBadge(false);
            populateFilters();
            applyFiltersAndRender();

        } catch (err) {
            console.error("Fetch API error:", err);

            // Fallback: Check if we have stale cached data
            const fallbackCache = localStorage.getItem(cacheKey);
            if (fallbackCache) {
                try {
                    rawData = JSON.parse(fallbackCache);
                    isOfflineCached = true;
                    updateStatusBadge(true);
                    populateFilters();
                    applyFiltersAndRender();
                    showLoading(false);
                    return;
                } catch (parseErr) {
                    // ignore
                }
            }

            showError(
                `Failed to fetch "${url}": ${err.message}. If running via file:// protocol, please open with a local web server (e.g. VS Code Live Server, Python http.server, or XAMPP) or clear browser cache.`
            );
        } finally {
            showLoading(false);
        }
    }

    function updateStatusBadge(fromCache) {
        if (!dataSourceBadge) return;
        if (fromCache) {
            dataSourceBadge.className = "cache-badge cached";
            dataSourceBadge.innerHTML = "⚡ Cached in localStorage (Offline Mode)";
        } else {
            dataSourceBadge.className = "cache-badge live";
            dataSourceBadge.innerHTML = "🟢 Live Fetch API";
        }
    }


    /* ======================================================================
       2. INTERMEDIATE EXTENSION: DEPENDENT DROPDOWNS (Country -> State -> City)
       ====================================================================== */

    function setupDependentDropdowns() {
        if (currentTab !== "students") {
            dependentDropdownsSection.style.display = "none";
            return;
        }

        dependentDropdownsSection.style.display = "block";

        // Extract unique countries using Array.map and Set
        const countries = [...new Set(rawData.map(s => s.country).filter(Boolean))].sort();

        countrySelect.innerHTML = `<option value="">-- All Countries (${countries.length}) --</option>` +
            countries.map(c => `<option value="${c}">${c}</option>`).join("");

        stateSelect.innerHTML = `<option value="">-- Select Country First --</option>`;
        stateSelect.disabled = true;

        citySelect.innerHTML = `<option value="">-- Select State First --</option>`;
        citySelect.disabled = true;
    }

    countrySelect.addEventListener("change", function () {
        const selectedCountry = this.value;

        if (!selectedCountry) {
            stateSelect.innerHTML = `<option value="">-- Select Country First --</option>`;
            stateSelect.disabled = true;
            citySelect.innerHTML = `<option value="">-- Select State First --</option>`;
            citySelect.disabled = true;
        } else {
            // Filter unique states in selected country
            const states = [...new Set(
                rawData
                    .filter(s => s.country === selectedCountry)
                    .map(s => s.state)
                    .filter(Boolean)
            )].sort();

            stateSelect.innerHTML = `<option value="">-- All States in ${selectedCountry} --</option>` +
                states.map(s => `<option value="${s}">${s}</option>`).join("");
            stateSelect.disabled = false;

            citySelect.innerHTML = `<option value="">-- Select State First --</option>`;
            citySelect.disabled = true;
        }

        applyFiltersAndRender();
    });

    stateSelect.addEventListener("change", function () {
        const selectedCountry = countrySelect.value;
        const selectedState = this.value;

        if (!selectedState) {
            citySelect.innerHTML = `<option value="">-- Select State First --</option>`;
            citySelect.disabled = true;
        } else {
            // Filter unique cities in selected state
            const cities = [...new Set(
                rawData
                    .filter(s => s.country === selectedCountry && s.state === selectedState)
                    .map(s => s.city)
                    .filter(Boolean)
            )].sort();

            citySelect.innerHTML = `<option value="">-- All Cities in ${selectedState} --</option>` +
                cities.map(c => `<option value="${c}">${c}</option>`).join("");
            citySelect.disabled = false;
        }

        applyFiltersAndRender();
    });

    citySelect.addEventListener("change", function () {
        applyFiltersAndRender();
    });


    /* ======================================================================
       3. POPULATE DYNAMIC FILTER DROPDOWNS
       ====================================================================== */

    function populateFilters() {
        if (!categoryFilter) return;

        let options = [];

        if (currentTab === "events") {
            categoryFilterLabel.textContent = "Category";
            options = [...new Set(rawData.map(e => e.category).filter(Boolean))].sort();
            categoryFilter.innerHTML = `<option value="ALL">All Categories</option>` +
                options.map(cat => `<option value="${cat}">${cat}</option>`).join("");

            // Setup Sort dropdown for events
            sortFilter.innerHTML = `
                <option value="default">Default Order</option>
                <option value="name-asc">Title: A to Z</option>
                <option value="name-desc">Title: Z to A</option>
                <option value="date-newest">Date: Newest First</option>
                <option value="date-oldest">Date: Oldest First</option>
                <option value="seats-desc">Seats: High to Low</option>
            `;
        } else if (currentTab === "students") {
            categoryFilterLabel.textContent = "Department";
            options = [...new Set(rawData.map(s => s.department).filter(Boolean))].sort();
            categoryFilter.innerHTML = `<option value="ALL">All Departments</option>` +
                options.map(dept => `<option value="${dept}">${dept}</option>`).join("");

            // Setup Sort dropdown for students
            sortFilter.innerHTML = `
                <option value="default">Default Order</option>
                <option value="name-asc">Name: A to Z</option>
                <option value="name-desc">Name: Z to A</option>
                <option value="cgpa-desc">CGPA: High to Low</option>
                <option value="cgpa-asc">CGPA: Low to High</option>
            `;

            setupDependentDropdowns();
        } else if (currentTab === "faqs") {
            categoryFilterLabel.textContent = "Topic";
            options = [...new Set(rawData.map(f => f.category).filter(Boolean))].sort();
            categoryFilter.innerHTML = `<option value="ALL">All Topics</option>` +
                options.map(cat => `<option value="${cat}">${cat}</option>`).join("");

            sortFilter.innerHTML = `
                <option value="default">Default Order</option>
                <option value="name-asc">Question: A to Z</option>
                <option value="name-desc">Question: Z to A</option>
            `;

            dependentDropdownsSection.style.display = "none";
        }
    }


    /* ======================================================================
       4. ARRAY METHODS: SEARCH, FILTER, SORT & PAGINATION LOGIC
       ====================================================================== */

    function applyFiltersAndRender() {
        const query = (searchInput.value || "").trim().toLowerCase();
        const selectedCategory = categoryFilter.value;
        const selectedSort = sortFilter.value;
        pageSize = parseInt(pageSizeSelect.value, 10) || 6;

        // --- ARRAY FILTER: Search & Category ---
        filteredData = rawData.filter(function (item) {
            // 1. Text Search across multiple fields using Array.filter & includes
            let matchesSearch = true;
            if (query !== "") {
                if (currentTab === "events") {
                    matchesSearch =
                        (item.title && item.title.toLowerCase().includes(query)) ||
                        (item.description && item.description.toLowerCase().includes(query)) ||
                        (item.department && item.department.toLowerCase().includes(query)) ||
                        (item.organizer && item.organizer.toLowerCase().includes(query)) ||
                        (item.venue && item.venue.toLowerCase().includes(query));
                } else if (currentTab === "students") {
                    matchesSearch =
                        (item.name && item.name.toLowerCase().includes(query)) ||
                        (item.rollNo && item.rollNo.toLowerCase().includes(query)) ||
                        (item.email && item.email.toLowerCase().includes(query)) ||
                        (item.city && item.city.toLowerCase().includes(query)) ||
                        (item.department && item.department.toLowerCase().includes(query));
                } else if (currentTab === "faqs") {
                    matchesSearch =
                        (item.question && item.question.toLowerCase().includes(query)) ||
                        (item.answer && item.answer.toLowerCase().includes(query));
                }
            }

            if (!matchesSearch) return false;

            // 2. Primary Category/Department Filter
            if (selectedCategory && selectedCategory !== "ALL") {
                if (currentTab === "events") {
                    if (item.category !== selectedCategory) return false;
                } else if (currentTab === "students") {
                    if (item.department !== selectedCategory) return false;
                } else if (currentTab === "faqs") {
                    if (item.category !== selectedCategory) return false;
                }
            }

            // 3. Dependent Location Filters (for Students tab)
            if (currentTab === "students") {
                const selCountry = countrySelect.value;
                const selState = stateSelect.value;
                const selCity = citySelect.value;

                if (selCountry && item.country !== selCountry) return false;
                if (selState && item.state !== selState) return false;
                if (selCity && item.city !== selCity) return false;
            }

            return true;
        });

        // --- ARRAY SORT: Sorting Logic ---
        if (selectedSort !== "default") {
            filteredData.sort(function (a, b) {
                switch (selectedSort) {
                    case "name-asc": {
                        const nameA = a.title || a.name || a.question || "";
                        const nameB = b.title || b.name || b.question || "";
                        return nameA.localeCompare(nameB);
                    }
                    case "name-desc": {
                        const nameA = a.title || a.name || a.question || "";
                        const nameB = b.title || b.name || b.question || "";
                        return nameB.localeCompare(nameA);
                    }
                    case "date-newest":
                        return new Date(b.date || 0) - new Date(a.date || 0);
                    case "date-oldest":
                        return new Date(a.date || 0) - new Date(b.date || 0);
                    case "seats-desc":
                        return (b.seats || 0) - (a.seats || 0);
                    case "cgpa-desc":
                        return (b.cgpa || 0) - (a.cgpa || 0);
                    case "cgpa-asc":
                        return (a.cgpa || 0) - (b.cgpa || 0);
                    default:
                        return 0;
                }
            });
        }

        // Reset to page 1 whenever filters change
        currentPage = 1;

        // Render current paginated slice
        renderView();
    }

    /**
     * Renders data using Array.slice() and Array.map()
     */
    function renderView() {
        const totalItems = filteredData.length;
        recordCountText.textContent = `Showing ${totalItems} of ${rawData.length} total records`;

        if (totalItems === 0) {
            dataGrid.innerHTML = "";
            faqList.innerHTML = "";
            emptyBox.style.display = "block";
            paginationContainer.style.display = "none";
            return;
        }

        emptyBox.style.display = "none";

        // --- ARRAY SLICE: Pagination Calculation ---
        const totalPages = Math.ceil(totalItems / pageSize) || 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalItems);
        const paginatedSlice = filteredData.slice(startIndex, endIndex);

        // Render cards
        if (currentTab === "events") {
            faqList.style.display = "none";
            dataGrid.style.display = "grid";
            dataGrid.innerHTML = paginatedSlice.map(renderEventCard).join("");
        } else if (currentTab === "students") {
            faqList.style.display = "none";
            dataGrid.style.display = "grid";
            dataGrid.innerHTML = paginatedSlice.map(renderStudentCard).join("");
        } else if (currentTab === "faqs") {
            dataGrid.style.display = "none";
            faqList.style.display = "flex";
            faqList.innerHTML = paginatedSlice.map(renderFaqCard).join("");
            setupFaqAccordion();
        }

        // Render Pagination UI
        renderPaginationUI(startIndex + 1, endIndex, totalItems, totalPages);
    }


    /* ======================================================================
       5. CARD TEMPLATES (HTML Template Strings)
       ====================================================================== */

    function renderEventCard(event) {
        return `
            <article class="event-card" data-id="${event.id}">
                <div>
                    <div class="event-card-header">
                        <span class="event-badge">${escapeHtml(event.category)}</span>
                        <span class="event-status">${escapeHtml(event.status || "Upcoming")}</span>
                    </div>

                    <h3>${escapeHtml(event.title)}</h3>

                    <div class="event-meta">
                        <div class="event-meta-item">
                            <span>📅</span>
                            <span><strong>Date:</strong> ${formatDate(event.date)} (${escapeHtml(event.time)})</span>
                        </div>
                        <div class="event-meta-item">
                            <span>📍</span>
                            <span><strong>Venue:</strong> ${escapeHtml(event.venue)}</span>
                        </div>
                        <div class="event-meta-item">
                            <span>🏛️</span>
                            <span><strong>Dept:</strong> ${escapeHtml(event.department)}</span>
                        </div>
                    </div>

                    <p class="event-desc">${escapeHtml(event.description)}</p>
                </div>

                <div class="event-footer">
                    <span>🪑 <strong>${event.seats}</strong> seats available</span>
                    <button class="btn-card-action" onclick="alert('Registration open for: ${escapeHtml(event.title).replace(/'/g, "\\'")}')">
                        Register
                    </button>
                </div>
            </article>
        `;
    }

    function renderStudentCard(student) {
        const initials = student.name
            .split(" ")
            .map(n => n[0])
            .join("")
            .slice(0, 2)
            .toUpperCase();

        return `
            <article class="student-card" data-id="${student.id}">
                <div>
                    <div class="student-card-header">
                        <div class="student-avatar">${initials}</div>
                        <div class="student-info">
                            <h3>${escapeHtml(student.name)}</h3>
                            <span class="student-roll">ID: ${escapeHtml(student.rollNo)}</span>
                        </div>
                    </div>

                    <div class="student-details">
                        <div><strong>Dept:</strong> ${escapeHtml(student.department)}</div>
                        <div><strong>Year:</strong> ${escapeHtml(student.year)}</div>
                        <div><strong>Semester:</strong> Sem ${student.semester}</div>
                        <div>
                            <strong>CGPA:</strong>
                            <span class="cgpa-pill">${student.cgpa.toFixed(2)}</span>
                        </div>
                    </div>

                    <div class="student-location">
                        📍 ${escapeHtml(student.city)}, ${escapeHtml(student.state)}, ${escapeHtml(student.country)}
                    </div>
                </div>

                <div class="event-footer">
                    <span style="font-size: 12px; opacity: 0.85;">📧 ${escapeHtml(student.email)}</span>
                    <button class="btn-card-action" onclick="alert('Viewing profile of: ${escapeHtml(student.name).replace(/'/g, "\\'")}')">
                        View
                    </button>
                </div>
            </article>
        `;
    }

    function renderFaqCard(faq) {
        return `
            <div class="faq-card">
                <div class="faq-header" role="button" tabindex="0" aria-expanded="false">
                    <span><strong>Q${faq.id}.</strong> ${escapeHtml(faq.question)}</span>
                    <span class="faq-icon">▼</span>
                </div>
                <div class="faq-body">
                    <p style="margin: 10px 0 0 0;">${escapeHtml(faq.answer)}</p>
                    <span style="display:inline-block; margin-top:8px; font-size:11px; padding:2px 8px; border-radius:10px; background:var(--button-bg);">
                        Category: ${escapeHtml(faq.category)}
                    </span>
                </div>
            </div>
        `;
    }

    function setupFaqAccordion() {
        const headers = faqList.querySelectorAll(".faq-header");
        headers.forEach(header => {
            header.addEventListener("click", function () {
                const card = this.parentElement;
                const isOpen = card.classList.contains("open");
                card.classList.toggle("open");
                this.setAttribute("aria-expanded", !isOpen);
            });
        });
    }


    /* ======================================================================
       6. PAGINATION CONTROLS
       ====================================================================== */

    function renderPaginationUI(start, end, total, totalPages) {
        paginationContainer.style.display = "flex";
        paginationInfo.textContent = `Showing ${start} to ${end} of ${total} records (Page ${currentPage} of ${totalPages})`;

        let buttonsHtml = "";

        // Previous button
        buttonsHtml += `
            <button class="page-btn" id="prevPageBtn" ${currentPage === 1 ? "disabled" : ""} aria-label="Previous Page">
                &laquo; Prev
            </button>
        `;

        // Numbered buttons
        for (let i = 1; i <= totalPages; i++) {
            buttonsHtml += `
                <button class="page-btn ${i === currentPage ? "active" : ""}" data-page="${i}">
                    ${i}
                </button>
            `;
        }

        // Next button
        buttonsHtml += `
            <button class="page-btn" id="nextPageBtn" ${currentPage === totalPages ? "disabled" : ""} aria-label="Next Page">
                Next &raquo;
            </button>
        `;

        paginationButtons.innerHTML = buttonsHtml;

        // Bind events
        document.getElementById("prevPageBtn").addEventListener("click", function () {
            if (currentPage > 1) {
                currentPage--;
                renderView();
                scrollToTop();
            }
        });

        document.getElementById("nextPageBtn").addEventListener("click", function () {
            if (currentPage < totalPages) {
                currentPage++;
                renderView();
                scrollToTop();
            }
        });

        paginationButtons.querySelectorAll(".page-btn[data-page]").forEach(btn => {
            btn.addEventListener("click", function () {
                currentPage = parseInt(this.getAttribute("data-page"), 10);
                renderView();
                scrollToTop();
            });
        });
    }

    function scrollToTop() {
        const toolbar = document.querySelector(".toolbar-card");
        if (toolbar) {
            toolbar.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }


    /* ======================================================================
       7. EVENT LISTENERS: TABS, FILTERS & CACHE CONTROLS
       ====================================================================== */

    // Tab Switching
    function switchTab(newTab) {
        if (currentTab === newTab) return;
        currentTab = newTab;

        [tabEvents, tabStudents, tabFaqs].forEach(tab => {
            tab.classList.remove("active");
            tab.setAttribute("aria-selected", "false");
        });

        if (newTab === "events") {
            tabEvents.classList.add("active");
            tabEvents.setAttribute("aria-selected", "true");
            searchInput.placeholder = "Search events by title, description, or department...";
        } else if (newTab === "students") {
            tabStudents.classList.add("active");
            tabStudents.setAttribute("aria-selected", "true");
            searchInput.placeholder = "Search students by name, roll number, or city...";
        } else if (newTab === "faqs") {
            tabFaqs.classList.add("active");
            tabFaqs.setAttribute("aria-selected", "true");
            searchInput.placeholder = "Search FAQs by question or answer keyword...";
        }

        // Clear filters
        searchInput.value = "";
        loadData(false);
    }

    tabEvents.addEventListener("click", () => switchTab("events"));
    tabStudents.addEventListener("click", () => switchTab("students"));
    tabFaqs.addEventListener("click", () => switchTab("faqs"));

    // Real-time Search input listener
    searchInput.addEventListener("input", function () {
        applyFiltersAndRender();
    });

    // Filter and Sort change listeners
    categoryFilter.addEventListener("change", applyFiltersAndRender);
    sortFilter.addEventListener("change", applyFiltersAndRender);
    pageSizeSelect.addEventListener("change", applyFiltersAndRender);

    // Reset Filters button
    resetFiltersBtn.addEventListener("click", function () {
        searchInput.value = "";
        categoryFilter.value = "ALL";
        sortFilter.value = "default";
        pageSizeSelect.value = "6";

        if (countrySelect) countrySelect.value = "";
        if (stateSelect) {
            stateSelect.innerHTML = `<option value="">-- Select Country First --</option>`;
            stateSelect.disabled = true;
        }
        if (citySelect) {
            citySelect.innerHTML = `<option value="">-- Select State First --</option>`;
            citySelect.disabled = true;
        }

        applyFiltersAndRender();
    });

    // Force Re-Fetch button
    forceRefreshBtn.addEventListener("click", function () {
        loadData(true);
    });

    // Clear Cache button
    clearCacheBtn.addEventListener("click", function () {
        localStorage.removeItem(CACHE_KEYS.events);
        localStorage.removeItem(CACHE_KEYS.students);
        localStorage.removeItem(CACHE_KEYS.faqs);
        alert("localStorage cache for Practical 6 cleared successfully. Re-fetching fresh data from server.");
        loadData(true);
    });

    // Retry Fetch button on error
    retryFetchBtn.addEventListener("click", function () {
        loadData(true);
    });


    /* ======================================================================
       8. UTILITY HELPERS
       ====================================================================== */

    function showLoading(show) {
        loadingBox.style.display = show ? "block" : "none";
        if (show) {
            dataGrid.style.display = "none";
            faqList.style.display = "none";
            emptyBox.style.display = "none";
            paginationContainer.style.display = "none";
        }
    }

    function showError(msg) {
        errorBox.style.display = "block";
        errorMessage.textContent = msg;
        dataGrid.style.display = "none";
        faqList.style.display = "none";
        paginationContainer.style.display = "none";
    }

    function hideError() {
        errorBox.style.display = "none";
        errorMessage.textContent = "";
    }

    function formatDate(dateStr) {
        if (!dateStr) return "TBA";
        const parts = dateStr.split("-");
        if (parts.length === 3) {
            const date = new Date(parts[0], parts[1] - 1, parts[2]);
            return date.toLocaleDateString("en-IN", {
                day: "numeric",
                month: "short",
                year: "numeric"
            });
        }
        return dateStr;
    }

    function escapeHtml(str) {
        if (!str) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    // --- INITIAL LOAD ---
    loadData(false);

})();
