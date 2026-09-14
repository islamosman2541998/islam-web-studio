// Only the Swiper modules the site uses, so the lazy slider chunk stays small.
// Core slider CSS ships in app.css to avoid a layout shift before sliders initialize.
export { default as Swiper } from 'swiper';
export { A11y, Autoplay, Keyboard, Navigation, Pagination } from 'swiper/modules';
