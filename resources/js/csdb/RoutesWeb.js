import { formDataToObject, isObject } from './helper';
import routes from '../../others/routes.json';

class RoutesWeb {
  // static #WebRoutes = routes;

  static get(name, data = {}){
    const route = Object.assign({}, routes[name]);

    // set route params
    if(data){      
      if(data.constructor.name === 'FormData') data = formDataToObject(data);
      Object.keys(data).forEach(k => {
        if(route.path.includes(`:${k}`)){
          route.path = route.path.replace(new RegExp(`:${k}\\??`), data[k]);
          delete data[k];
        }
      });
    }

    // clear the path
    route.path = route.path.replace(/(:\/\/)|(\/)+/g, "$1$2"); // untuk menghilangkan multiple slash
    
    // set route url and data
    if (!route.method || route.method.includes('GET')) {
      const url = new URL(window.location.origin + route.path);
      url.search = new URLSearchParams(data);
      route.url = url.toString();
      route.data = Object.assign({}, route.data); // supaya tidak ada Proxy
    }
    else if(route.method.includes('POST')){
      route.data = data;
      route.url = new URL(window.location.origin + route.path).toString();
    }

    route.method = Object.assign({}, route.method); // supaya tidak ada Proxy, sehingga worker bisa pakainya
    return route;
  }
}

export default RoutesWeb;