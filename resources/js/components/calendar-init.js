import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import listPlugin from "@fullcalendar/list";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";

export function calendarInit() {
    const calendarWrapper = document.querySelector("#calendar");

    if (calendarWrapper) {
        const newDate = new Date();
        const getDynamicMonth = () => {
            const month = newDate.getMonth() + 1;
            return month < 10 ? `0${month}` : `${month}`;
        };

        // Modal Elements
        const getModalTitleEl = document.querySelector("#event-title");
        const getModalStartDateEl = document.querySelector("#event-start-date");
        const getModalEndDateEl = document.querySelector("#event-end-date");
        const getModalAddBtnEl = document.querySelector(".btn-add-event");
        const getModalUpdateBtnEl = document.querySelector(".btn-update-event");
        const getModalHeaderEl = document.querySelector("#eventModalLabel");

        // Helper: Format Date +1 Day
        const addOneDay = (dateStr) => {
            if (!dateStr) return null;
            const [year, month, day] = dateStr.split("-").map(Number);
            const date = new Date(year, month - 1, day);
            date.setDate(date.getDate() + 1);
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, "0");
            const d = String(date.getDate()).padStart(2, "0");
            return `${y}-${m}-${d}`;
        };

        // Static Events Data
        const calendarEventsList = [
            {
                id: "1",
                title: "Contoh Event",
                start: `${newDate.getFullYear()}-${getDynamicMonth()}-01`,
                end: addOneDay(
                    `${newDate.getFullYear()}-${getDynamicMonth()}-03`,
                ),
                extendedProps: { calendar: "Danger" },
            },
            {
                id: "2",
                title: "Seminar #4",
                start: `${newDate.getFullYear()}-${getDynamicMonth()}-07`,
                end: `${newDate.getFullYear()}-${getDynamicMonth()}-10`,
                extendedProps: { calendar: "Success" },
            },
        ];

        // --- Responsiveness Logic ---
        const isMobile = () => window.innerWidth < 768;

        const getHeaderConfig = () => {
            if (isMobile()) {
                return {
                    left: "prev,next",
                    center: "title",
                    right: "addEventButton", // Fokus pada aksi tambah di mobile
                };
            }
            return {
                left: "prev,next addEventButton",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            };
        };

        // Modal Logic
        const openModal = () => {
            const modal = document.getElementById("eventModal");
            if (modal) {
                modal.style.display = "flex";
                document.body.style.overflow = "hidden";
            }
        };

        const closeModal = () => {
            const modal = document.getElementById("eventModal");
            if (modal) {
                modal.style.display = "none";
                document.body.style.overflow = "";
            }
            resetModalFields();
        };

        function resetModalFields() {
            if (getModalTitleEl) getModalTitleEl.value = "";
            if (getModalStartDateEl) getModalStartDateEl.value = "";
            if (getModalEndDateEl) getModalEndDateEl.value = "";
            const checkedRadio = document.querySelector(
                'input[name="event-level"]:checked',
            );
            if (checkedRadio) checkedRadio.checked = false;
        }

        const calendarSelect = (info) => {
            resetModalFields();
            if (getModalHeaderEl) getModalHeaderEl.textContent = "Add Event";
            if (getModalAddBtnEl) getModalAddBtnEl.style.display = "flex";
            if (getModalUpdateBtnEl) getModalUpdateBtnEl.style.display = "none";
            if (getModalStartDateEl) getModalStartDateEl.value = info.startStr;
            if (getModalEndDateEl)
                getModalEndDateEl.value = info.endStr || info.startStr;
            openModal();
        };

        const calendarAddEvent = () => {
            resetModalFields();
            if (getModalHeaderEl) getModalHeaderEl.textContent = "Add Event";
            if (getModalAddBtnEl) getModalAddBtnEl.style.display = "flex";
            if (getModalUpdateBtnEl) getModalUpdateBtnEl.style.display = "none";
            const today = new Date().toISOString().split("T")[0];
            if (getModalStartDateEl) getModalStartDateEl.value = today;
            openModal();
        };

        const calendarEventClick = (info) => {
            const eventObj = info.event;
            resetModalFields();
            if (getModalHeaderEl) getModalHeaderEl.textContent = "Edit Event";
            if (getModalTitleEl) getModalTitleEl.value = eventObj.title;
            if (getModalStartDateEl)
                getModalStartDateEl.value = eventObj.startStr.split("T")[0];
            if (getModalEndDateEl)
                getModalEndDateEl.value = eventObj.endStr
                    ? eventObj.endStr.split("T")[0]
                    : "";

            const level = eventObj.extendedProps.calendar;
            const radio = document.querySelector(`input[value="${level}"]`);
            if (radio) radio.checked = true;

            if (getModalUpdateBtnEl) {
                getModalUpdateBtnEl.dataset.fcEventPublicId = eventObj.id;
                getModalUpdateBtnEl.style.display = "flex";
            }
            if (getModalAddBtnEl) getModalAddBtnEl.style.display = "none";
            openModal();
        };

        // --- Initialize Calendar ---
        const calendar = new Calendar(calendarWrapper, {
            plugins: [
                dayGridPlugin,
                timeGridPlugin,
                listPlugin,
                interactionPlugin,
            ],
            selectable: true,
            initialView: isMobile() ? "listMonth" : "dayGridMonth",
            headerToolbar: getHeaderConfig(),
            events: calendarEventsList,
            select: calendarSelect,
            eventClick: calendarEventClick,
            displayEventTime: false,
            stickyHeaderDates: true,
            handleWindowResize: true,
            windowResizeDelay: 100,

            // Update tampilan saat layar berubah
            windowResize: function (view) {
                if (isMobile()) {
                    calendar.changeView("listMonth");
                    calendar.setOption("headerToolbar", getHeaderConfig());
                    calendar.setOption("customButtons", {
                        addEventButton: { text: "+", click: calendarAddEvent },
                    });
                } else {
                    calendar.changeView("dayGridMonth");
                    calendar.setOption("headerToolbar", getHeaderConfig());
                    calendar.setOption("customButtons", {
                        addEventButton: {
                            text: "Add Event +",
                            click: calendarAddEvent,
                        },
                    });
                }
            },

            customButtons: {
                addEventButton: {
                    text: isMobile() ? "+" : "Add Event +",
                    click: calendarAddEvent,
                },
            },

            eventContent(eventInfo) {
                const colorClass = `fc-bg-${(eventInfo.event.extendedProps.calendar || "primary").toLowerCase()}`;
                return {
                    html: `
                        <div class="event-fc-color flex fc-event-main ${colorClass} p-1 rounded-sm">
                            <div class="fc-event-title" style="overflow: hidden; text-overflow: ellipsis;">
                                ${eventInfo.event.title}
                            </div>
                        </div>
                    `,
                };
            },
        });

        calendar.render();

        // Update Event Listener
        if (getModalUpdateBtnEl) {
            getModalUpdateBtnEl.addEventListener("click", () => {
                const id = getModalUpdateBtnEl.dataset.fcEventPublicId;
                const event = calendar.getEventById(id);
                const level =
                    document.querySelector('input[name="event-level"]:checked')
                        ?.value || "";

                if (event) {
                    event.remove();
                    calendar.addEvent({
                        id: id,
                        title: getModalTitleEl.value,
                        start: getModalStartDateEl.value,
                        end: getModalEndDateEl.value,
                        allDay: true,
                        extendedProps: { calendar: level },
                    });
                }
                closeModal();
            });
        }

        // Add Event Listener
        if (getModalAddBtnEl) {
            getModalAddBtnEl.addEventListener("click", () => {
                const level =
                    document.querySelector('input[name="event-level"]:checked')
                        ?.value || "";
                calendar.addEvent({
                    id: Date.now().toString(),
                    title: getModalTitleEl.value,
                    start: getModalStartDateEl.value,
                    end: getModalEndDateEl.value,
                    allDay: true,
                    extendedProps: { calendar: level },
                });
                closeModal();
            });
        }

        // Global Modal Closers
        document
            .querySelectorAll(".modal-close-btn")
            .forEach((btn) => btn.addEventListener("click", closeModal));
        window.addEventListener("click", (e) => {
            if (e.target === document.getElementById("eventModal"))
                closeModal();
        });
    }
}

export default calendarInit;
