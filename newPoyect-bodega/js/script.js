
const body = document.querySelector("body"),
      sidebar = body.querySelector(".sidebar"),
      toggle = body.querySelector(".toggle"),
      searchBtn = body.querySelector(".search-box"),
      modeSwitch = body.querySelector(".toggle-switch"),
      modeText = body.querySelector(".mode-text");

// Recuperar la preferencia del modo y del sidebar del almacenamiento local
const savedMode = localStorage.getItem("mode");
const savedSidebarState = localStorage.getItem("sidebarState");

// Aplicar la preferencia del modo
if (savedMode) {
    body.classList.add(savedMode);
    if (savedMode === "dark") {
        modeText.innerText = "Light Mode";
    } else {
        modeText.innerText = "Dark Mode";
    }
}

// Aplicar el estado del sidebar
if (savedSidebarState === "close") {
    sidebar.classList.add("close");
}

// Alternar el estado del sidebar y guardarlo en localStorage
toggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");

    if (sidebar.classList.contains("close")) {
        localStorage.setItem("sidebarState", "close");
    } else {
        localStorage.setItem("sidebarState", "open");
    }
});

// Alternar el modo y guardarlo en localStorage
modeSwitch.addEventListener("click", () => {
    body.classList.toggle("dark");

    if (body.classList.contains("dark")) {
        modeText.innerText = "Light Mode";
        localStorage.setItem("mode", "dark");
    } else {
        modeText.innerText = "Dark Mode";
        localStorage.setItem("mode", "light");
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Obtener la ruta actual de la página
    const currentPath = window.location.pathname;

    // Comparar la ruta actual con las rutas de los enlaces
    if (currentPath.includes('inicio.php')) {
        document.querySelector('.nav-inicio').parentElement.classList.add('active');
    } else if (currentPath.includes('index_recibo.php')) {
        document.querySelector('.nav-bienes').parentElement.classList.add('active');
    } else if (currentPath.includes('index.php')) {
        document.querySelector('.nav-proveedor').parentElement.classList.add('active');
    } else if (currentPath.includes('detalles_recibo.php')) {
        document.querySelector('.nav-nuevo').parentElement.classList.add('active');
    }else if (currentPath.includes('saliente.php')) {
        document.querySelector('.nav-saliente').parentElement.classList.add('active');
    }else if (currentPath.includes('saliente_detalles.php')) {
        document.querySelector('.nav-saliente').parentElement.classList.add('active');
    }else if (currentPath.includes('documento.php')) {
        document.querySelector('.nav-documento').parentElement.classList.add('active');
    }else if (currentPath.includes('codigo_barra.php')) {
        document.querySelector('.nav-barcode').parentElement.classList.add('active');
    }else if (currentPath.includes('categoria.php')) {
        document.querySelector('.nav-categoria').parentElement.classList.add('active');
    }else if (currentPath.includes('producto.php')) {
        document.querySelector('.nav-producto').parentElement.classList.add('active');
    }
});

