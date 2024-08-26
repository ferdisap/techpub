function storingResponse(axiosResponse){
  if(axiosResponse.statusText === 'OK'){
    this.data.list = axiosResponse.data.data;
    delete axiosResponse.data.data;
    this.data.paginationInfo = axiosResponse.data;
  }
}

function clickFilename(event, filename){
  if (!this.CB.selectionMode) {
    this.$router.push({
      name: 'Dispatch',
      params: {filename: filename}
    });
    this.emitter.emit('clickFilenameFromDispatchList', { filename: filename }) // key filename saja karena bisa diambil dari techpubstore atau server jika perlu
  }
}

export {storingResponse, clickFilename};