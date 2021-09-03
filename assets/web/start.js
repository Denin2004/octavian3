import React from 'react';
import ReactDOM from 'react-dom';
import { BrowserRouter as Router } from 'react-router-dom';

import App from '@app/web/App';
import '@app/web/less/app.less';

ReactDOM.render(
    <div className="App">
        <Router>
            <App/>
        </Router>
    </div>,
    document.getElementById('root')
);
