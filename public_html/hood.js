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

const indexOfPage = (state, page_name) =>
    state.page_list.indexOf(state.current_page);

const hood = (initial_state) =>
{
  const set_state = (new_state) =>
  {
    const state = {...initial_state, ...new_state};
    render(state);
  };

  const render = (state) =>
  {
    const iframes = document.querySelectorAll('iframe');
    const handles = document.querySelectorAll('li.handle');

    iframes.forEach(toggle_out(indexOfPage(state), 'hidden'));
    handles.forEach(toggle_in(indexOfPage(state), 'selected'));
  }

  const selectSection = (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');

    element.onclick = () =>
      set_state({current_page: initial_state.page_list[position]});

    element.ondblclick = () =>
      iframes[position].src += '';
  };

  const reloadSection = (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');

    element.querySelectorAll('i.reloader').forEach( (e) => e.onclick =
      () => iframes[position].src += '');
  }

  const inspectSection = (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');
    element.querySelectorAll('i.inspector').forEach( (e) => e.onclick =
      () => window.open(iframes[position].src, '_blank'));
  }

  (() =>
  {
    const handles = document.querySelectorAll('li.handle');
    handles.forEach(selectSection);
    handles.forEach(reloadSection);
    handles.forEach(inspectSection);
  }
  )();
};

window.hood = hood;
