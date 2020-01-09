//Les imports importants
import React from "react";
import ReactDOM from "react-dom";
import Navbar from "./components/Navbar";
import HomePage from "./pages/HomePage";

require('../css/app.css');
require('../css/bootstrap.min.css');

console.log('Hello Webpack Encore!!! Edit me in assets/js/app.js');

const App = () => {
    return(
        <>
            <Navbar/>;
            <div className="container pt-5">
                <HomePage/>
            </div>
        </>
    );
};

const rootElement = document.querySelector('#app');
ReactDOM.render(<App />, rootElement);
