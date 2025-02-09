$(function () {
    function ini_events(ele) {
        ele.each(function () {
            var eventObject = {
                title: $.trim($(this).text()),
            };
            $(this).data("eventObject", eventObject);

            $(this).draggable({
                zIndex: 1070,
                revert: true,
                revertDuration: 0,
            });
        });
    }

    ini_events($("#external-events div.external-event"));

    $("#calendar").fullCalendar({
        locale: "es",
        header: {
            left: "prev,next today",
            center: "title",
            right: "month,agendaWeek,agendaDay",
        },
        editable: true,
        droppable: true,
        height: 700,
        events: requisitosUrl, // URL para cargar eventos dinámicos
        eventClick: function (event) {
            // Muestra los datos en el modal
            $("#eventTitle").text(event.title); // Título del evento
            $("#eventObligacion").text(event.obligacion); // Título del evento
            $("#eventDate").text(event.start.format("YYYY-MM-DD")); // Fecha del evento
            $("#eventDescription").text(
                event.description || "No hay descripción disponible."
            ); // Descripción
            $("#eventResponsable").text(event.responsable || "No asignado"); // Responsable del evento

            // Lógica para mostrar el estado aprobado en verde/rojo
            const eventApproved = $("#eventApproved");
            if (event.approved == 1) {
                eventApproved.text(
                    "Esta evidencia ha sido marcada como revisada."
                ); // Texto en verde
                eventApproved
                    .removeClass("bg-danger")
                    .addClass("bg-success text-white"); // Verde
            } else {
                eventApproved.text(
                    "Esta evidencia no ha sido revisada o volvió a su estatus inicial."
                ); // Texto en rojo
                eventApproved
                    .removeClass("bg-success")
                    .addClass("bg-danger text-white"); // Rojo
            }

            // Muestra el modal
            $("#eventModal").modal("show");
        },
        eventRender: function (event, element) {
            element.attr("title", event.title); // Tooltip con el título del evento
        },
        eventRender: function (event, element) {
            // Lógica para cambiar el color según condiciones
            if (event.approved == 1) {
                element.css("background-color", "#28a745"); // Verde
                element.css("border-color", "#28a745");
            } else {
                element.css("background-color", "#dc3545"); // Rojo
                element.css("border-color", "#dc3545");
            }
            element.attr("title", event.title); // Tooltip con el título
        },
    });

    $("#color-chooser > li > a").click(function (e) {
        e.preventDefault();
        var currColor = $(this).css("color");
        $("#add-new-event").css({
            "background-color": currColor,
            "border-color": currColor,
        });
    });

    $("#add-new-event").click(function (e) {
        e.preventDefault();
        var val = $("#new-event").val();
        if (!val.length) return;

        var event = $("<div />");
        event
            .css({
                "background-color": "#3c8dbc",
                "border-color": "#3c8dbc",
                color: "#fff",
            })
            .addClass("external-event")
            .text(val);
        $("#external-events").prepend(event);
        ini_events(event);
        $("#new-event").val("");
    });
});
