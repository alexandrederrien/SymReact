//Les imports importants
import React from "react";
import ReactDOM from "react-dom";

require('../css/app.css');

console.log('Hello Webpack Encore!!! Edit me in assets/js/app.js');

const App = () => {
    return <h1>Bonjour à tous</h1>;
};

const rootElement = document.querySelector('#app');
ReactDOM.render(<App />, rootElement);
