import "./bootstrap";
import Alpine from "alpinejs";
import ApexCharts from "apexcharts";

// flatpickr
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";
// FullCalendar
import { Calendar } from "@fullcalendar/core";

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

Alpine.start();

// Initialize components on DOM ready
document.addEventListener("DOMContentLoaded", () => {
    // Map imports
    if (document.querySelector("#mapOne")) {
        import("./components/map").then((module) => module.initMap());
    }

    // Chart imports
    if (document.querySelector("#chartOne")) {
        import("./components/chart/chart-1").then((module) =>
            module.initChartOne(),
        );
    }
    if (document.querySelector("#chartTwo")) {
        import("./components/chart/chart-2").then((module) =>
            module.initChartTwo(),
        );
    }
    if (document.querySelector("#chartThree")) {
        import("./components/chart/chart-3").then((module) =>
            module.initChartThree(),
        );
    }
    if (document.querySelector("#chartSix")) {
        import("./components/chart/chart-6").then((module) =>
            module.initChartSix(),
        );
    }
    if (document.querySelector("#chartEight")) {
        import("./components/chart/chart-8").then((module) =>
            module.initChartEight(),
        );
    }
    if (document.querySelector("#chartThirteen")) {
        import("./components/chart/chart-13").then((module) =>
            module.initChartThirteen(),
        );
    }

    // Calendar init
    if (document.querySelector("#calendar")) {
        import("./components/calendar-init").then((module) =>
            module.calendarInit(),
        );
    }
});

function updateTime() {
    const now = new Date();

    // Ambil komponen waktu secara manual
    const days = [
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
    ];
    const months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ];

    const dayName = days[now.getDay()];
    const dayDate = now.getDate();
    const monthName = months[now.getMonth()];
    const year = now.getFullYear();

    // Logika 12 jam (AM/PM)
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");
    const ampm = hours >= 12 ? "PM" : "AM";

    hours = hours % 12;
    hours = hours ? hours : 12; // Jam '0' jadi '12'
    const hoursStr = String(hours).padStart(2, "0");

    // Rakit formatnya secara bebas di sini
    const finalString = `${dayName}, ${dayDate} ${monthName} ${year} ${hoursStr}:${minutes}:${seconds} ${ampm}`;
    document.getElementById("realtime-clock").innerText = finalString;
}

setInterval(updateTime, 1000);
updateTime(); // Jalankan langsung
