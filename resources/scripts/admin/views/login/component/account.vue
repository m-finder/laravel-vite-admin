<template>
  <el-form :model="dataForm" ref="dataFormRef" :rules="rules" size="large" class="login-content-form">
    <el-form-item class="login-animation1" prop="account">
      <el-input type="text" :placeholder="$t('message.account.accountPlaceholder1')" v-model="dataForm.account" clearable autocomplete="off">
        <template #prefix>
          <el-icon class="el-input__icon">
            <ele-User/>
          </el-icon>
        </template>
      </el-input>
    </el-form-item>
    <el-form-item class="login-animation2" prop="password">
      <el-input :type="isShowPassword ? 'text' : 'password'" :placeholder="$t('message.account.accountPlaceholder2')" v-model="dataForm.password" autocomplete="off">
        <template #prefix>
          <el-icon class="el-input__icon">
            <ele-Unlock/>
          </el-icon>
        </template>
        <template #suffix>
          <i class="iconfont el-input__icon login-content-password" :class="isShowPassword ? 'icon-yincangmima' : 'icon-xianshimima'" @click="isShowPassword = !isShowPassword"></i>
        </template>
      </el-input>
    </el-form-item>
    <el-form-item class="login-animation3" prop="captcha">
      <el-col :span="15">
        <el-input type="text" maxlength="4" :placeholder="$t('message.account.accountPlaceholder3')" v-model="dataForm.captcha" clearable autocomplete="off">
          <template #prefix>
            <el-icon class="el-input__icon">
              <ele-Position/>
            </el-icon>
          </template>
        </el-input>
      </el-col>
      <el-col :span="1"></el-col>
      <el-col :span="8">
        <el-button class="login-content-code" @click="getCaptcha()">
          <el-image v-loading="captchaLoading" :src="captchaPath" fit="fill"></el-image>
        </el-button>
      </el-col>
    </el-form-item>
    <el-form-item class="login-animation4">
      <el-button type="primary" class="login-content-submit" round @click="onSignIn" :loading="loading.signIn">
        <span>{{ $t('message.account.accountBtnText') }}</span>
      </el-button>
    </el-form-item>
  </el-form>
</template>

<script lang="ts">
import {toRefs, reactive, defineComponent, computed, onMounted, getCurrentInstance} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import {useI18n} from 'vue-i18n';
import Cookies from 'js-cookie';
import {storeToRefs} from 'pinia';
import {useThemeConfig} from '@admin/stores/themeConfig';
import {initFrontEndControlRoutes} from '@admin/router/frontEnd';
import {initBackEndControlRoutes} from '@admin/router/backEnd';
import {Session} from '@admin/utils/storage';
import {formatAxis} from '@admin/utils/formatTime';
import {NextLoading} from '@admin/utils/loading';
import {useLoginApi} from '@admin/api/login';
import logo from '@admin/assets/logo.png';

// ????????????????????????????????????
interface LoginMobileState {
  account: any;
  captcha_key: string | undefined;
  captcha: string | number | undefined;
  password: string | number | undefined;
  is_password: boolean;
}

// ?????????????????????
const dataForm: LoginMobileState = {
  account: '',
  password: '',
  captcha_key: '',
  captcha: '',
  is_password: true
};

export default defineComponent({
  name: 'loginAccount',
  setup() {
    const {t} = useI18n();
    const storesThemeConfig = useThemeConfig();
    const {themeConfig} = storeToRefs(storesThemeConfig);
    const {proxy} = <any>getCurrentInstance();
    const route = useRoute();
    const router = useRouter();
    const state = reactive({
      dataForm,
      captchaLoading: false,
      captchaPath: undefined,
      isShowPassword: false,
      rules: {
        account: {required: true, message: '??????????????????????????????', trigger: 'blur'},
        password: {required: true, message: '?????????????????????', trigger: 'blur'},
        captcha: {required: true, message: '??????????????????', trigger: 'blur'},
      },
      loading: {
        signIn: false,
      },
    });

    // ????????????
    const currentTime = computed(() => {
      return formatAxis(new Date());
    });

    // ???????????????
    onMounted(() => {
      getCaptcha();
    });

    const loginApi = useLoginApi();
    // ???????????????
    const getCaptcha = () => {
      if (state.dataForm.captcha_key) {
        state.dataForm.captcha_key = undefined
      }
      state.captchaLoading = true
      loginApi.getCaptcha().then((res) => {
        state.captchaLoading = false
        state.dataForm.captcha_key = res.data.key
        state.captchaPath = res.data.img
      }).catch((err?) => {
        state.captchaLoading = false
        proxy.$notify.error({
          title: '??????',
          message: err.msg
        })
      })
    };
    // ??????
    const onSignIn = () => {
      proxy.$refs.dataFormRef.validate((valid: boolean) => {
        if (valid) {

          state.loading.signIn = true;
          loginApi.signIn(state.dataForm).then(async (res) => {
            // ?????? token ??????????????????
            Session.set('token', res.data.token);
            // ?????? `/src/stores/userInfo.ts` ?????????????????????????????????????????????
            Cookies.set('userId', res.data.admin.id);
            Cookies.set('userName', res.data.admin.name);
            Cookies.set('avatar', res.data.admin.avatar || logo);
            // ?????????????????????2????????????????????????
            await initFrontEndControlRoutes();
            signInSuccess();
          }).catch((err?) => {
            state.loading.signIn = false;
            proxy.$notify.error({
              title: '??????',
              message: err.msg
            })
          })

        } else {
          return false;
        }
      });


    };
    // ????????????????????????
    const signInSuccess = () => {
      // ????????????????????????????????????
      let currentTimeInfo = currentTime.value;
      // ??????????????????????????????
      // ??????????????????????????????????????????/???????????????????????????????????????????????????????????????
      if (route.query?.redirect) {
        router.push({
          path: <string>route.query?.redirect,
          query: Object.keys(<string>route.query?.params).length > 0 ? JSON.parse(<string>route.query?.params) : '',
        });
      } else {
        router.push('/');
      }
      // ??????????????????
      // ?????? loading
      state.loading.signIn = true;
      const signInText = t('message.signInText');
      proxy.$message.success(`${currentTimeInfo}???${signInText}`);
      // ?????? loading???????????????????????????????????????????????????
      NextLoading.start();
    };
    return {
      getCaptcha,
      onSignIn,
      ...toRefs(state),
    };
  },
});
</script>

<style scoped lang="scss">
.login-content-form {
  margin-top: 20px;
  @for $i from 1 through 4 {
    .login-animation#{$i} {
      opacity: 0;
      animation-name: error-num;
      animation-duration: 0.5s;
      animation-fill-mode: forwards;
      animation-delay: calc($i/10) + s;
    }
  }

  .login-content-password {
    display: inline-block;
    width: 20px;
    cursor: pointer;

    &:hover {
      color: #909399;
    }
  }

  .login-content-code {
    width: 100%;
    padding: 0;
    font-weight: bold;
    letter-spacing: 5px;
  }

  .login-content-submit {
    width: 100%;
    letter-spacing: 2px;
    font-weight: 300;
    margin-top: 15px;
  }
}
</style>
