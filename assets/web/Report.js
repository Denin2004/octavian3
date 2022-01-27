import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Spin } from 'antd';

import axios from 'axios';

import Query from '@app/web/report/Query';

class Report extends Component {
    constructor(props){
        super(props);
        this.state = {
            report: null
        };
    }
    
    componentDidMount() {
        axios.get(
            generatePath(window.mfwApp.urls.report.metaData+'/:id', {id: this.props.match.params.id}),
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(res => {
            console.log(res.data);
            this.setState({report: res.data.report});
        }).catch(error => {
            if (error.response) {
                console.log(error);
            } else {
                console.log(error);
            }
        });
        
    }

    render() {
        return this.state.report == null ? <Spin/> :
            <React.Fragment>
                {this.state.report.formQuery != undefined ? <Query query={this.state.report.formQuery} title={'dddd'} /> : null} 
            </React.Fragment>        
    }
}

export default Report;