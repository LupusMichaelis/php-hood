window.addEventListener('load', () =>
{
  const iframes = document.querySelectorAll('iframe');
  const handles = document.querySelectorAll('li.handle');

  const toggle_out = (offset, className) =>
    ( element, position) =>
      (
        ( position === offset && element.classList.contains(className) )
          ||
        ( position !== offset && !element.classList.contains(className) )
      ) && element.classList.toggle(className);

  const toggle_in = (offset, className) =>
    ( element, position) =>
      (
        ( position !== offset && element.classList.contains(className) )
          ||
        ( position === offset && !element.classList.contains(className) )
      ) && element.classList.toggle(className);

  const selectSection = (element, position) =>
  {
    element.onclick = () =>
    {
      iframes.forEach(toggle_out(position, 'hidden'));
      handles.forEach(toggle_in(position, 'selected'));
    }

    element.ondblclick = () =>
      iframes[position].src += '';
  };

  const reloadSection = (element, position) =>
    element.querySelectorAll('i.reloader').forEach( (e) => e.onclick =
      () => iframes[position].src += '');

  const inspectSection = (element, position) =>
    element.querySelectorAll('i.inspector').forEach( (e) => e.onclick =
      () => window.open(iframes[position].src, '_blank'));

  handles.forEach(selectSection);
  handles.forEach(reloadSection);
  handles.forEach(inspectSection);

  if(null === document.querySelector('li.handle.selected'))
    handles[0].click();
});
