import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Spin, Layout, message } from 'antd';

import axios from 'axios';
import { withTranslation } from 'react-i18next';

import Query from '@app/web/report/Query';

class Report extends Component {
    constructor(props){
        super(props);
        this.state = {
            report: null,
            queryText: ''
        };
        this.getData = this.getData.bind(this);
        this.getQueryText = this.getQueryText.bind(this);
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
                message.error(error);
            } else {
                message.error(error);
            }
        });
    }
    
    getData(values) {
        this.setState({queryText: this.getQueryText(values)})
    }
    
    getQueryText(values) {
        var text = '';
        Object.keys(this.state.report.formQuery).map(key => {
            switch(this.state.report.formQuery[key].type) {
                case 'mfw-currency':
                case 'mfw-location':
                case 'mfw-choice':
                    var value = '';
                    if (this.state.report.formQuery[key].multiple) {
                        var vals = this.state.report.formQuery[key].choices.filter(choice => values[this.state.report.formQuery[key].full_name].includes(choice.value));
                        vals.map(v => value=value+v.label+',');
                        value = value.slice(0, -1);
                    } else {
                        value = this.state.report.formQuery[key].choices.find(choice => choice.value == values[this.state.report.formQuery[key].full_name]).label;
                    }
                    text = text+this.props.t(this.state.report.formQuery[key].label)+': '+value+'.';
                    break;
                case 'mfw-range':
                    var format = this.state.report.formQuery[key].options.addTime ? window.mfwApp.formats.datetime : window.mfwApp.formats.date;
                    text = text+this.props.t('date._from')+' '+ values[this.state.report.formQuery[key].full_name][0].format(format)+' '+
                      this.props.t('date._to').toLowerCase()+' '+ values[this.state.report.formQuery[key].full_name][1].format(format)+'.';
                    break;
            }
        })
        return text;
    }

    render() {
        return <React.Fragment>
            {this.state.report == null ? <Layout.Content><Spin/></Layout.Content> :
            <React.Fragment>
                {this.state.report.formQuery != undefined ? <Layout.Header theme="light">
                    <Query 
                      query={this.state.report.formQuery} 
                      title={this.state.report.title}
                      queryText={this.state.queryText}
                      success={this.getData}/>
                </Layout.Header> : null} 
                <Layout.Content>Report body</Layout.Content>
            </React.Fragment>}
        </React.Fragment>
    }
}

export default withTranslation()(Report);