import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import { Spin, Layout, message } from 'antd';

import axios from 'axios';
import { withTranslation } from 'react-i18next';
import moment from 'moment-timezone';

import Query from '@app/web/report/Query';
import TableResult from '@app/web/report/result/Table';
import {ajaxResponse} from '@app/web/report/ajaxResponse/declare';

class Report extends Component {
    constructor(props){
        super(props);
        this.state = {
            report: null,
            queryText: '',
            result: null,
            loading: false,
            ajaxResponse: {
                success: true,
                key: 0,
                data: []
            }
        };
        this.getData = this.getData.bind(this);
        this.getQueryText = this.getQueryText.bind(this);
        this.getMetaData = this.getMetaData.bind(this);
        this.showAjaxResponse = this.showAjaxResponse.bind(this);
        this.setAjaxResponse = this.setAjaxResponse.bind(this);
    }

    componentDidMount() {
        this.getMetaData();
    }

    getMetaData() {
        axios.get(
            generatePath(window.mfwApp.urls.report.metaData+'/:id', {id: this.props.match.params.id}),
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        ).then(res => {
            this.setState({report: res.data.report});
            if ((res.data.report.autoLoad != undefined)&&(res.data.report.autoLoad === false)) {
                if (res.data.report.formQuery != undefined) {

                } else {
                    this.getData([]);
                }
            }
        }).catch(error => {
            if (error.response) {
                message.error(error);
            } else {
                message.error(error);
            }
        });
    }

    componentDidUpdate(prev) {
        if (prev.match.params.id != this.props.match.params.id) {
            this.setState({
                report: null,
                queryText: '',
                result: null,
                loading: false
            });
            this.getMetaData();
        }
    }

    getData(values) {
        this.setState({loading: true});
        axios({
            method: 'post',
            url: generatePath(window.mfwApp.urls.report.data+'/:id', {id: this.props.match.params.id}),
            data: values,
            headers: {'Content-Type': 'application/json','X-Requested-With': 'XMLHttpRequest'}
        }).then(res => {
            if (res.data.success) {
                this.setState({
                    queryText: this.getQueryText(values),
                    result: res.data.result,
                    loading: false
                });
            } else {
                message.error(this.props.t(res.data.error));
            }
        }).catch(error => {
            message.error(error.toString());
        });
    }

    getQueryText(values) {
        if (!this.state.report.formQuery) {
            return '';
        }
        var text = '';
        Object.keys(this.state.report.formQuery).map(key => {
            switch(this.state.report.formQuery[key].type) {
                case 'mfw-currency':
                case 'mfw-location':
                case 'mfw-choice':
                    var value = '';
                    if (this.state.report.formQuery[key].multiple) {
                        var vals = this.state.report.formQuery[key].choices.filter(choice => values[this.state.report.formQuery[key].name].includes(choice.value));
                        vals.map(v => value=value+v.label+',');
                        value = value.slice(0, -1);
                    } else {
                        value = this.state.report.formQuery[key].choices.find(choice => choice.value == values[this.state.report.formQuery[key].name]).label;
                    }
                    text = text+this.props.t(this.state.report.formQuery[key].label)+': '+value+'.';
                    break;
                case 'mfw-range':
                    var format = this.state.report.formQuery[key].options.addTime ? window.mfwApp.formats.datetime : window.mfwApp.formats.date;
                    text = text+this.props.t('date._from')+' '+ values[this.state.report.formQuery[key].name][0].format(format)+' '+
                      this.props.t('date._to').toLowerCase()+' '+ values[this.state.report.formQuery[key].name][1].format(format)+'.';
                    break;
            }
        })
        return text;
    }

    showAjaxResponse() {
        const Response = ajaxResponse[this.state.report.ajaxResponse ? this.state.report.ajaxResponse : 'default'];
        return <Response response={this.state.ajaxResponse}/>
    }
    
    setAjaxResponse(response) {
        /*check for report refresh*/
        response.key = this.state.ajaxResponse.key + 1;
        this.setState({ajaxResponse: response});
    }

    render() {
        return (
            this.state.report == null ? <Layout.Content><Spin/></Layout.Content> :
            <React.Fragment>
                {this.state.report.formQuery != undefined ? <Layout.Header theme="light">
                    <Query
                      query={this.state.report.formQuery}
                      title={this.state.report.title}
                      queryText={this.state.queryText}
                      success={this.getData}/>
                </Layout.Header> : null}
                <Layout.Content>
                    {this.state.report.results.map((result, i) => {
                        return <TableResult
                          key={i}
                          tableConfig={result.tableConfig}
                          loading={this.state.loading}
                          ajaxResponse={this.setAjaxResponse}
                          data={this.state.result != null ? this.state.result[i]: []}/>
                    })}
                </Layout.Content>
                {this.showAjaxResponse()}
            </React.Fragment>
        );
    }
}

export default withTranslation()(Report);