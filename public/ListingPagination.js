export default function ListingPagination(thisPage, lastPage) {

  const fillNav = (start, stop) => {
    let array = [], i = stop - start + 1;
    while (i--) {
       array[i] = stop--;
    }
    return array;
  }

  let buttons;
  if (lastPage > 9) {
    if (thisPage < 5) {
      buttons = fillNav(1, Math.max(thisPage + 1, 4));
      buttons.push('...', lastPage);
    } else if (lastPage - thisPage < 4) {
      buttons = fillNav(Math.min(thisPage - 1, lastPage - 3), lastPage);
      buttons.unshift(1, '...');
    } else {
      buttons = [1, '...', thisPage - 1, thisPage, thisPage + 1, '...', lastPage];
    }
  } else {
    buttons = fillNav(1, lastPage);
  }

  ['p', ...buttons, 'n'].forEach(text => {
    let attribs = { class: 'nav_s' };
    switch (text) {
      case 'p':
      case 'n':
        attribs.class = 'nav_' + text;
        text = null;
        break;
      case '...':
        attribs.class = 'nav_sep';
        break;
      default:
        attribs['data-page'] = text;
    }

    text !== null && $('<a/>', attribs).appendTo(this).text(text);
  });

};