import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Spin, Layout, message } from 'antd';

import axios from 'axios';
import { withTranslation } from 'react-i18next';


class MachineInfo extends Component {
    constructor(props){
        super(props);
        this.state = {
        };
    }

    render() {
        console.log(this.props.info);
        return (
            <React.Fragment>
            Machine info
            </React.Fragment>
        );
    }
}

export default withTranslation()(MachineInfo);