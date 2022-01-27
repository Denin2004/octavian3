import React, {Component} from 'react';

import { Form, message } from 'antd';

import axios from 'axios';
import { withTranslation } from 'react-i18next';

import MfwFormWidget from '@app/mfw/mfwForm/MfwFormWidget';

class MfwForm extends Component {
    
    constructor(props){
        super(props);
        this.state = {
        }
    }

    closeError() {
        this.setState({error: ''});
    }

    finish(values) {
        axios({
            method: this.props.mfwForm.method,
            url: this.props.mfwForm.action,
            data: values
        }).then(res => {
            if (res.data.success) {
                this.props.success(res.data);
            } else {
                message.error(this.props.t(res.data.error));
            }
        }).catch(error => {
            message.error(error.toString());
        });
    }

    findWidget(child, parsed) {
        const children = React.Children.toArray(child.props ? (child.props.children ? child.props.children : []) : []);
        if ((child.props)&&(child.props.element)&&(child.props.element.id)) {
            parsed.push(child.props.element.id);
        }
        children.map(ch => {
            this.findWidget(ch, parsed);
        });
    }

    render() {
        return (
            <Form onFinish={this.finish}>
            </Form>
        )
    }
}

export default withTranslation()(MfwForm);