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


const inspectSection = (element, position) =>
{
  const iframes = document.querySelectorAll('iframe');
  element.querySelectorAll('i.inspector').forEach( (e) => e.onclick =
    () => window.open(iframes[position].src, '_blank'));
}

const reloadSection = (element, position) =>
{
  const iframes = document.querySelectorAll('iframe');

  element.querySelectorAll('i.reloader').forEach( (e) => e.onclick =
    () => iframes[position].src += '');
}

const check_state = (given_state) =>
{
  const virgin_state =
  { current_page: null
  , page_list: []
  };

  return {...virgin_state, ...given_state};
};

const hood = (given_state) =>
{
  const initial_state = check_state(given_state);

  const set_state = (new_state) =>
  {
    const state = {...initial_state, ...new_state};
    render(state);
  };

  const selectSection = (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');

    element.onclick = () =>
      set_state({current_page: initial_state.page_list[position]});

    element.ondblclick = () =>
      iframes[position].src += '';
  };

  const render = (state) =>
  {
    const iframes = document.querySelectorAll('iframe');
    iframes.forEach(toggle_out(indexOfPage(state), 'hidden'));

    const handles = document.querySelectorAll('li.handle');
    handles.forEach(toggle_in(indexOfPage(state), 'selected'));

    handles.forEach(selectSection);
    handles.forEach(reloadSection);
    handles.forEach(inspectSection);
  };

  render(initial_state);
};

window.hood = hood;
