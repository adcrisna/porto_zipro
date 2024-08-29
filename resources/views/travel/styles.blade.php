<style>
.lds-roller {
    display: inline-block;
    position: relative;
    width: 80px;
    height: 80px;
}

.lds-roller div {
    animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    transform-origin: 40px 40px;
}

.lds-roller div:after {
    content: " ";
    display: block;
    position: absolute;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #263570;
    margin: -4px 0 0 -4px;
}

.lds-roller div:nth-child(1) {
    animation-delay: -0.036s;
}

.lds-roller div:nth-child(1):after {
    top: 63px;
    left: 63px;
}

.lds-roller div:nth-child(2) {
    animation-delay: -0.072s;
}

.lds-roller div:nth-child(2):after {
    top: 68px;
    left: 56px;
}

.lds-roller div:nth-child(3) {
    animation-delay: -0.108s;
}

.lds-roller div:nth-child(3):after {
    top: 71px;
    left: 48px;
}

.lds-roller div:nth-child(4) {
    animation-delay: -0.144s;
}

.lds-roller div:nth-child(4):after {
    top: 72px;
    left: 40px;
}

.lds-roller div:nth-child(5) {
    animation-delay: -0.18s;
}

.lds-roller div:nth-child(5):after {
    top: 71px;
    left: 32px;
}

.lds-roller div:nth-child(6) {
    animation-delay: -0.216s;
}

.lds-roller div:nth-child(6):after {
    top: 68px;
    left: 24px;
}

.lds-roller div:nth-child(7) {
    animation-delay: -0.252s;
}

.lds-roller div:nth-child(7):after {
    top: 63px;
    left: 17px;
}

.lds-roller div:nth-child(8) {
    animation-delay: -0.288s;
}

.lds-roller div:nth-child(8):after {
    top: 56px;
    left: 12px;
}

@keyframes lds-roller {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.page-header {
    height: 0vh;
}

.header-filter:before {
    background-color: rgb(218, 219, 219)
}

.form-check .form-check-label .circle .check {
    background-color: #0d59fc;
}

.form-check .form-check-input:checked~.circle {
    border-color: rgb(118, 118, 118);
}

.form-check.form-check-radio>.form-check-label {
    color: black !important;
}

.form-check .form-check-input:checked~.form-check-sign .check {
    background: #0d59fc;
}

.media .avatar {
    width: 160px;
    height: 159px;
    padding: 15px;
}

:root {
    --custom-primary-color: #1363B4;
}

.bg-section {
    background: #FAFAFA;
}

.btn-primary,
.background-color,
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active,
.btn-primary.active,
.dropdown-toggle.btn-primary {
    background-color: var(--custom-primary-color);
    border-color: var(--custom-primary-color);
}

.text-primary {
    color: var(--custom-primary-color);
}

.bg-primary-2 {
    background-color: #1363B4;
}

.bg-primary-3 {
    background-color: #0CBC8B;
}

.bg-primary-4 {
    background-color: #263570;
}

.bg-primary-4:active {
    background-color: #263570;
}

.bg-primary-4:hover {
    background-color: #263570;
}

.text-custom-primary-color {
    color: var(--custom-primary-color);
}

.type-1 {
    border-radius: 10px;
    border: 2px solid #eee;
    transition: .3s border-color;
    height: 45px;
}

.type-1::placeholder {
    color: rgb(136, 136, 136);
}

.type-1:hover {
    border: 2px solid #aaa;
    border-radius: 10px;
}

.sub-judul {
    border-radius: 10px;
    border: 2px solid #eee;
    height: 45px;
}

body {
    font-family: 'Helvetica Neue', sans-serif;
    text-align: justify;
    text-justify: inter-word;
}

.bootstrap-select .select-with-transition,
.bootstrap-select .btn:active,
.bootstrap-select .btn.active {
    background-image: linear-gradient(to top, #1363B4 2px, rgba(156, 39, 176, 0) 2px), linear-gradient(to top, rgba(249, 249, 249, 0.26) 1px, transparent 1px);
}

.btn-pill-2 {
    border-radius: 50px;
}

.btn-info-2 {
    background-color: #263570;
    border-color: #263570;
    color: #fff;
}

.btn-info-2:hover,
.btn-info-2:focus {
    background-color: #17255f !important;
    border-color: #17255f;
}

.btn-secondary-2 {
    color: #263570;
    background-color: #fafafa;
    border: 1px solid #263570;
    font-weight: 500;
    box-shadow: 0 2px 2px 0 rgba(250, 250, 250, 0.14), 0 3px 1px -2px rgba(250, 250, 250, 0.2), 0 1px 5px 0 rgba(250, 250, 250, 0.12);
}

.btn-secondary-2:hover,
.btn-secondary-2:focus {
    color: #263570 !important;
    background-color: #fafafa !important;
    border-color: #17255f;
}

.btn-info-1 {
    background-color: #1363B4;
    border-color: #1363B4;
    color: #fff;
}

.color-info-1 {
    color: #1363B4;
}

.btn-info-1:hover,
.btn-info-1:focus {
    background-color: #0f4e99 !important;
    border-color: #0f4e99;
}

.nav-item {
    font-family: inter, sans-serif;
}

.table-borderless tbody+tbody,
.table-borderless td,
.table-borderless th,
.table-borderless thead th {
    border: 0;
}

</style>
