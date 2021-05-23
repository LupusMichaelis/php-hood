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

const actions =
{ open_inspector: (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');
    element.querySelectorAll('i.inspector').forEach( (e) => e.onclick =
      () => window.open(iframes[position].src, '_blank'));
  }

, reload_tab: (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');

    element.querySelectorAll('i.reload_tab').forEach( (e) => e.onclick =
      () => iframes[position].src += '');
  }

, close_tab: (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');

    element.querySelectorAll('i.close_tab').forEach( (e) => e.onclick =
      () =>
      {
        iframes[position].parentNode.removeChild(iframes[position]);
        element.parentNode.removeChild(element);
      });
  }
, select_tab: ({set_state, initial_state}) =>
  (element, position) =>
  {
    const iframes = document.querySelectorAll('iframe');

    element.onclick = () =>
      set_state({current_page: initial_state.page_list[position]});

    element.ondblclick = () =>
      iframes[position].src += '';

    const anchor_list = element.querySelectorAll('a');
    anchor_list.forEach((tag) => tag.onclick = (ev) => ev.preventDefault());
  }
};

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

  const render = (state) =>
  {
    const search_part = new URLSearchParams({current: state.current_page});
    window.history.pushState(search_part, '', '?' + search_part);

    const iframes = document.querySelectorAll('iframe');
    iframes.forEach(toggle_out(indexOfPage(state), 'hidden'));

    const handles = document.querySelectorAll('li.handle');
    handles.forEach(toggle_in(indexOfPage(state), 'selected'));

    [ actions.select_tab({set_state, initial_state})
    , actions.reload_tab
    , actions.close_tab
    , actions.open_inspector
    ].forEach(handles.forEach.bind(handles));
  };

  render(initial_state);
};

window.hood = hood;
