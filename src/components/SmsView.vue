<template>
  <k-inside>
    <k-view class="k-mailjet-sms-view">
      <k-header>Mailjet SMS</k-header>
      <k-fieldset
        v-model="sms"
        :fields="{
          to: {
            label: this.$t('mailjet.sms.to'),
            type: 'text',
            placeholder: this.$t('mailjet.sms.to.placeholder'),
            width: '1/2',
            required: true,
          },
          from: {
            label: this.$t('mailjet.sms.from'),
            type: 'text',
            placeholder: this.$t('mailjet.sms.from.placeholder'),
            width: '1/2',
            // required: true,
          },
          message: {
            label: this.$t('mailjet.sms.message'),
            maxlength: message.maxlength,
            required: true,
            type: 'textarea',
            buttons: false,
          },
        }"
      />
      <div class="buttonwrapper">
        <k-button
          class="smsbutton"
          @click="onClick()"
          :icon="currenticon"
          :disabled="!cansend"
          :class="{
            sending: sending,
            'has-error': hasError,
            'is-success': isSuccess,
          }"
          >{{ this.$t("mailjet.sms.action") }}</k-button
        >
      </div>
    </k-view>
  </k-inside>
</template>

<script>
export default {
  data: function () {
    return {
      sms: {
        from: "",
        to: "",
        message: "",
      },
      message: {
        maxlength: 0,
      },
      cooldown: Number,
      sending: false,
      hasError: false,
      isSuccess: false,
    };
  },
  created() {
    this.$api
      .get("mailjet/sms/config")
      .then((response) => {
        this.sms.from = response.from;
        this.message.maxlength = response.maxlength;
        this.cooldown = response.cooldown;
      })
      .catch((error) => {
        console.log(error);
      });
  },
  computed: {
    currenticon: function () {
      if (this.hasError) return "alert";
      if (this.isSuccess) return "check";
      return "mailjet-sms";
    },
    cansend: function () {
      return (
        this.sms.to.length > 0 &&
        this.sms.message.length > 0 &&
        this.sms.message.length <= this.message.maxlength
      );
    },
  },
  methods: {
    onClick: function () {
      this.sending = true;
      this.$api
        .post("mailjet/sms/send", this.sms)
        .then((response) => {
          if (response.statusCode === 200) {
            this.handleSuccess();
            this.sms.to = "";
          } else {
            this.handleError();
          }
          this.sending = false;
        })
        .catch((error) => {
          this.sending = false;
          console.log(error);
        });
    },
    handleSuccess: function () {
      let that = this;
      that.isSuccess = true;
      setTimeout(function () {
        that.isSuccess = false;
      }, that.cooldown);
    },
    handleError: function () {
      let that = this;
      that.hasError = true;
      setTimeout(function () {
        that.hasError = false;
      }, that.cooldown);
    },
  },
};
</script>

<style lang="scss" scoped>
.buttonwrapper {
  margin-top: 3.5rem;
}

.smsbutton {
  background-color: black;
  color: white;
  border-radius: 3px;
  padding: 0.5rem 1rem;
  line-height: 1.25rem;
  text-align: left;
}

.smsbutton:hover {
  background-color: #1e1e1e;
}

.smsbutton > .k-button-text {
  opacity: 1;
}

.smsbutton.sending {
  background-color: #dcdcdc;

  &:after {
    content: "...";
  }
}

.smsbutton.sending > .k-button-text {
  color: black;
}

.smsbutton.has-response {
  background-color: #999;
}

.smsbutton.is-success {
  background-color: #5d800d;
}

.smsbutton.has-error {
  background-color: #d16464;
}
</style>
