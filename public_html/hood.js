'use strict';

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


const section_inspector = (element, position) =>
{
  const iframes = document.querySelectorAll('iframe');
  element.querySelectorAll('i.inspector').forEach( (e) => e.onclick =
    () => window.open(iframes[position].src, '_blank'));
}

const section_reloader = (element, position) =>
{
  const iframes = document.querySelectorAll('iframe');

  element.querySelectorAll('i.reloader').forEach( (e) => e.onclick =
    () => iframes[position].src += '');
}

const section_remover = (element, position) =>
{
  const iframes = document.querySelectorAll('iframe');

  element.querySelectorAll('i.remover').forEach( (e) => e.onclick =
    () =>
    {
      iframes[position].parentNode.removeChild(iframes[position]);
      element.parentNode.removeChild(element);
    });
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

  const section_selecter = (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');

    element.onclick = () =>
      set_state({current_page: initial_state.page_list[position]});

    element.ondblclick = () =>
      iframes[position].src += '';

    const anchor_list = element.querySelectorAll('a');
    anchor_list.forEach((tag) => tag.onclick = (ev) => ev.preventDefault());
  };

  const render = (state) =>
  {
    const search_part = new URLSearchParams({current: state.current_page});
    window.history.pushState(search_part, '', '?' + search_part);

    const iframes = document.querySelectorAll('iframe');
    iframes.forEach(toggle_out(indexOfPage(state), 'hidden'));

    const handles = document.querySelectorAll('li.handle');
    handles.forEach(toggle_in(indexOfPage(state), 'selected'));

    handles.forEach(section_selecter);
    handles.forEach(section_reloader);
    handles.forEach(section_remover);
    handles.forEach(section_inspector);
  };

  render(initial_state);
};

window.hood = hood;
