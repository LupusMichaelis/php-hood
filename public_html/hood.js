'use strict';

import {toggle_out, toggle_in, cancel_default} from './helpers.js';

const indexOfPage = (state, page_name) =>
  state.tab_list.indexOf(state.current_tab);

const refresh_iframe = (iframe) =>
  iframe.src += '';

const actions =
{ open_inspector:({iframes}) =>
  (element, position) =>
    element.querySelectorAll('a.inspect-tab').forEach( (e) => e.onclick =
      cancel_default(() => window.open(iframes[position].src, '_blank')))
, reload_tab:({iframes}) =>
  (element, position) =>
    element.querySelectorAll('a.reload-tab').forEach( (e) => e.onclick =
      cancel_default(() => refresh_iframe(iframes[position])))
, close_tab: ({iframes}) =>
  (element, position) =>
    element.querySelectorAll('a.close-tab').forEach( (e) => e.onclick =
      cancel_default(() =>
      {
        iframes[position].parentNode.removeChild(iframes[position]);
        element.parentNode.removeChild(element);
      }))
, select_tab: ({iframes, set_state, initial_state}) =>
  (element, position) =>
  {
    element.onclick = cancel_default(() => set_state({current_tab: initial_state.tab_list[position]}));

    element.ondblclick = () =>
      refresh_iframe(iframes[position]);
  }
};

const check_state = (given_state) =>
{
  const virgin_state =
  { current_tab: null
  , tab_list: []
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
    const search_part = new URLSearchParams({current: state.current_tab});
    window.history.pushState('' + search_part, '', '?' + search_part);

    const iframes = document.querySelectorAll('iframe');
    iframes.forEach(toggle_out(indexOfPage(state), 'hidden'));

    const handles = document.querySelectorAll('li.handle');

    [ actions.select_tab({iframes, set_state, initial_state})
    , actions.reload_tab({iframes})
    , actions.close_tab({iframes})
    , actions.open_inspector({iframes})
    , toggle_in(indexOfPage(state), 'selected')
    ].forEach(handles.forEach.bind(handles));
  };

  render(initial_state);
};

export default hood;
