const bottomBarItems = {
  FolderDispatch: {
    iconName: 'folder',
    tooltipName: 'Folder',
    isShow: false,
    data: {},
    type: undefined,
  }
}

const colWidth = {
  satu: { portion: 0.5 },
  dua: { portion: 0.5 }, // 0.5 karena col2 dan col3 ada pada satu div yang sama biar memudahkan resizing
}

function col1Width() {
  return `width:${this.colWidth['satu']['portion'] * 100}%`;
}
function col2Width() {
  return `width:${(1 - this.colWidth['satu']['portion']) * 100}%`;
}

export { bottomBarItems, colWidth, col1Width, col2Width };

// export { bottomBarItems, colWidth, col1Width, col2Width, col3Width, turnOnSizing, turnOffSizing, colSize, col2, col3 };