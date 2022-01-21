import React, {Component} from 'react';
import {generatePath} from 'react-router-dom';

import axios from 'axios';

class Report extends Component {
    constructor(props){
        super(props);
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
            console.log(res.data.report.formQuery);
        }).catch(error => {
            if (error.response) {
                console.log(error);
            } else {
                console.log(error);
            }
        });
        
    }

    render() {
        return (
                <div>Report {this.props.match.params.id}</div>
        )
    }
}

export default Report;