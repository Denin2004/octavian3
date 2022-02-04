import React, {Component} from 'react';

import { Form, Select } from 'antd';

import { withTranslation } from 'react-i18next';

class CurrencyChoice extends Component {
    
    constructor(props){
        super(props);
        var choices = [...props.field.choices];
        if (this.props.field.all === true) {
            choices[0].label = props.t(choices[0].label);
        }
        this.state = {
            choices: choices
        };
    }
    
    render() {
        return (
            <Form.Item name={this.props.field.name}
              label={this.props.t(this.props.field.label)}
              initialValue={this.props.field.value == '' ? (this.state.choices.length != 0 ? this.state.choices[0].value : null) : this.props.field.value}>
                <Select  options={this.state.choices}/>
            </Form.Item>
        )
    }
}

export default withTranslation()(CurrencyChoice);