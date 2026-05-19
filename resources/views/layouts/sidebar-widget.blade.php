<div class="mx-auto mb-5 w-auto rounded-2xl bg-gray-50 px-4 py-5 text-center dark:bg-white/[0.03]">


        <span class="text-gray-500 text-xs dark:text-gray-400" id="realtime-clock">Memuat waktu...</span>
 
  

</div>

<script>
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

</script>
