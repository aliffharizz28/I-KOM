<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-KOM @yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('pic/logoikomputih.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/stylesidebar.css') }}?v=2.0">
    <style>
        /* CSS Tambahan untuk mengawal layout besar */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Elakkan scroll horizontal */
            font-family: 'Inter', sans-serif; /* Set default as Inter */
            color: #333;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif; /* Set headers as Montserrat */
        }
        .main-layout {
            display: flex; /* Memastikan sidebar dan content sebelah-menyebelah */
            min-height: 100vh;
            width: 100%;
        }
        .content-area {
            flex: 1;
            margin-left: 280px; /* Match sidebar width */
            background-color: #f8f9fa;
            padding: 40px;
            box-sizing: border-box;
            transition: margin-left 0.3s ease-in-out;
        }
        /* When sidebar is collapsed, content fills the full width */
        .content-area.sidebar-hidden {
            margin-left: 0;
        }


        
        /* Interactive Sortable Headers */
        th.sortable-header {
            cursor: pointer;
            position: relative;
            user-select: none;
            padding-right: 28px !important;
            transition: background-color 0.2s ease;
        }
        th.sortable-header:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }
        th.sortable-header::after {
            content: "\f0dc";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.35;
            font-size: 0.8em;
        }
        th.sortable-header[data-sort-dir="asc"]::after {
            content: "\f0de";
            opacity: 0.9;
        }
        th.sortable-header[data-sort-dir="desc"]::after {
            content: "\f0dd";
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="main-layout">
        
        @include('sidebar')

        <div class="content-area">
            @include('topbar')
            @yield('content')
            @include('bottombar')
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {


            // 2. Automatically apply sortable class to headers (excluding action columns or empty headers)
            document.querySelectorAll("table").forEach(function(table) {
                if (table.classList.contains("no-sort")) return;
                
                const headers = table.querySelectorAll("thead th");
                headers.forEach(function(th) {
                    const text = th.textContent.trim().toLowerCase();
                    // Skip if header is empty, contains action-like names, or is explicitly ignored
                    if (th.classList.contains("no-sort") || !text || text === "tindakan" || text === "action" || text === "edit" || text === "padam") {
                        return;
                    }
                    th.classList.add("sortable-header");
                });
            });
        });

        // 3. Global click delegate handler for sorting table columns
        document.addEventListener("click", function(e) {
            const th = e.target.closest("th.sortable-header");
            if (!th) return;

            const table = th.closest("table");
            if (!table) return;

            const tbody = table.querySelector("tbody");
            if (!tbody) return;

            const rows = Array.from(tbody.querySelectorAll("tr"));
            if (rows.length <= 1) return; // Nothing to sort

            const columnIndex = Array.from(th.parentNode.children).indexOf(th);
            const isAscending = th.getAttribute("data-sort-dir") !== "asc";

            // Clear sort state on siblings
            th.parentNode.querySelectorAll("th").forEach(function(sibling) {
                sibling.removeAttribute("data-sort-dir");
            });

            // Set current sort state
            th.setAttribute("data-sort-dir", isAscending ? "asc" : "desc");

            // Sort logic
            rows.sort(function(rowA, rowB) {
                const cellA = rowA.children[columnIndex];
                const cellB = rowB.children[columnIndex];

                if (!cellA || !cellB) return 0;

                const textA = cellA.textContent.trim();
                const textB = cellB.textContent.trim();

                // Number parsing
                const cleanA = textA.replace(/[^\d.-]/g, "");
                const cleanB = textB.replace(/[^\d.-]/g, "");
                const numA = parseFloat(cleanA);
                const numB = parseFloat(cleanB);

                if (!isNaN(numA) && !isNaN(numB) && cleanA !== "" && cleanB !== "") {
                    return isAscending ? numA - numB : numB - numA;
                }

                // Text compare
                return isAscending
                    ? textA.localeCompare(textB, undefined, { numeric: true, sensitivity: "base" })
                    : textB.localeCompare(textA, undefined, { numeric: true, sensitivity: "base" });
            });

            // Reorder
            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        });
    </script>
</body>
</html>