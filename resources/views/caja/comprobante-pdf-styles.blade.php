* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 9px;
    color: #000;
    padding: 6px 10px 6px 6px; /* más aire a la derecha */
    width: 100%;
}

.centrado { text-align: center; }
.negrita  { font-weight: bold; }
.izq      { text-align: left; }
.der      { text-align: right; padding-right: 2px; }

.nombre-restaurante {
    font-size: 12px;
    margin-bottom: 4px;
}

.separador {
    text-align: center;
    letter-spacing: -1px;
    margin: 4px 0;
    font-size: 8px;
    overflow: hidden;
    white-space: nowrap;
}

table.detalle {
    width: 96%;
    table-layout: fixed;
    border-collapse: collapse;
    margin: 4px 0;
    font-size: 8px;
}
table.detalle thead th {
    padding: 2px 0;
    font-size: 8px;
    font-weight: bold;
    white-space: nowrap;
}
table.detalle thead th:nth-child(1) { width: 30%; }
table.detalle thead th:nth-child(2) { width: 35%; }
table.detalle thead th:nth-child(3) { width: 35%; }
table.detalle tbody td {
    padding: 1px 0;
    white-space: nowrap;
}
.nombre-producto {
    font-weight: bold;
    padding-top: 4px !important;
    font-size: 9px;
    white-space: normal;
}

table.totales {
    width: 96%;
    table-layout: fixed;
    font-size: 9px;
    margin: 4px 0;
}
table.totales td {
    padding: 2px 0;
}
table.totales td.izq { width: 55%; }
table.totales td.der { width: 45%; padding-right: 2px; }
table.totales tr.total td {
    font-size: 11px;
    border-top: 1px dashed #000;
    padding-top: 4px;
}

.footer {
    margin-top: 8px;
    font-size: 8px;
}