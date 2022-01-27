import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Dropdown } from 'antd';
import { DownOutlined } from '@ant-design/icons';

import axios from 'axios';

import useWithForm from '@app/web/mfw/mfwForm/MfwFormHOC';

class Query extends Component {
    constructor(props){
        super(props);
        this.state = {
            text: ''
        };
        this.buildForm = this.buildForm.bind(this);
    }
    
    buildForm() {
        return <div>Query</div>
    }

    render() {
        return <React.Fragment>
            <Dropdown overlay={this.buildForm}>
                <a className="ant-dropdown-link" onClick={e => e.preventDefault()}>
                    {this.props.title} <DownOutlined />
                </a>
            </Dropdown>
        </React.Fragment>        
    }
}

export default useWithForm(Query);