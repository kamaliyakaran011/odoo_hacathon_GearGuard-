<?php
include 'db.php';
include 'header.php';
?>

<!-- FullCalendar CSS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<div class="h-full flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-800">Operational Calendar</h2>
            <p class="text-slate-500">Track maintenance schedules and active requests.</p>
        </div>
        <div class="flex space-x-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-600">Planned Service</span>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">High Priority</span>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-600">Medium Priority</span>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex-1">
        <div id='calendar' class="h-[600px]"></div>
    </div>
</div>

<!-- Event Details Modal -->
<div id="eventModal" class="fixed inset-0 bg-slate-900/50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-8 w-96 shadow-2xl transform transition-all scale-100">
        <div class="flex justify-between items-start mb-4">
            <h3 id="modalTitle" class="text-xl font-bold text-slate-800">Event Details</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-red-500 font-bold text-xl">&times;</button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase">Date</label>
                <p id="modalDate" class="font-bold text-slate-700"></p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase">Type</label>
                <p id="modalType" class="text-sm text-slate-600"></p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase">Details</label>
                <p id="modalDetail" class="text-sm text-slate-600"></p>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
            <button onclick="closeModal()"
                class="bg-slate-100 text-slate-600 px-4 py-2 rounded-xl font-bold hover:bg-slate-200 transition-colors">Close</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'standard',
            height: '100%',
            events: 'api_events.php', // Load events from our API
            eventClick: function (info) {
                // Populate and show modal
                document.getElementById('modalTitle').innerText = info.event.title;
                document.getElementById('modalDate').innerText = info.event.start.toLocaleDateString();
                document.getElementById('modalType').innerText = info.event.extendedProps.type;
                document.getElementById('modalDetail').innerText = info.event.extendedProps.detail;

                document.getElementById('eventModal').classList.remove('hidden');
                document.getElementById('eventModal').classList.add('flex');
            },
            eventDidMount: function (info) {
                // Add tooltip-like behavior or simple styling checks here if needed
            }
        });
        calendar.render();
    });

    function closeModal() {
        document.getElementById('eventModal').classList.add('hidden');
        document.getElementById('eventModal').classList.remove('flex');
    }

    // Close modal on click outside
    document.getElementById('eventModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Custom CSS for FullCalendar to match OneUI/Dasher
    const style = document.createElement('style');
    style.innerHTML = `
        .fc-button-primary {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #64748b !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            padding: 0.5rem 1rem !important;
            text-transform: capitalize !important;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
        }
        .fc-button-primary:hover {
            background-color: #f8fafc !important;
            color: #10b981 !important;
            border-color: #10b981 !important;
        }
        .fc-button-active {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: white !important;
        }
        .fc-daygrid-day {
            transition: background-color 0.2s;
        }
        .fc-daygrid-day:hover {
            background-color: #f8fafc;
        }
        .fc-event {
            border: none !important;
            border-radius: 6px !important;
            padding: 2px 4px !important;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            cursor: pointer;
        }
    `;
    document.head.appendChild(style);
</script>

</main>
</div>
</body>

</html>