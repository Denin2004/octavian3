import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Spin, Layout } from 'antd';

import axios from 'axios';
import { withTranslation } from 'react-i18next';

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
        return <React.Fragment>
            {this.state.report == null ? <Layout.Content><Spin/></Layout.Content> :
            <React.Fragment>
                {this.state.report.formQuery != undefined ? <Layout.Header>
                    <Query query={this.state.report.formQuery} title={this.state.report.title} />
                </Layout.Header> : null} 
                <Layout.Content>Report body</Layout.Content>
            </React.Fragment>}
        </React.Fragment>
    }
}

export default withTranslation()(Report);