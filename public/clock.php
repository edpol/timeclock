<main>
    <div class="face">
        <svg id="dial" viewBox="-100 -100 200 200" role="img" aria-label="Analog clock face">
            <g id="ticks"></g>
            <g id="nums"></g>
            <line id="hh" class="hand hour" x1="0" y1="5" x2="0" y2="-48" stroke-width="5"/>
            <line id="mh" class="hand minute" x1="0" y1="7" x2="0" y2="-70" stroke-width="3.5"/>
            <line id="sh" class="hand second" x1="0" y1="14" x2="0" y2="-76"/>
            <circle class="hub" r="4.5"/>
        </svg>
    </div>

    <div class="readout" aria-live="off">
        <div class="time" id="time">--:--:--</div>
        <div class="date" id="date"></div>
        <div class="zone" id="zone"></div>
    </div>

</main>
