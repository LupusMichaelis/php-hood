'use strict';

const toggle_out = (offset, className) =>
  (element, position) =>
    (
      (position === offset && element.classList.contains(className))
        ||
      (position !== offset && !element.classList.contains(className))
    ) && element.classList.toggle(className);

const toggle_in = (offset, className) =>
  (element, position) =>
    (
      (position !== offset && element.classList.contains(className))
        ||
      (position === offset && !element.classList.contains(className))
    ) && element.classList.toggle(className);

const cancel_default = (cb) => (ev) =>
{
  ev.preventDefault();
  cb();
  return false;
};

export {cancel_default, toggle_in, toggle_out};
