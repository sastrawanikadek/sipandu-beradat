package com.sipanduberadat.petugas.components

import android.content.Context
import android.os.Handler
import android.os.Looper
import android.util.AttributeSet
import android.util.TypedValue
import android.view.LayoutInflater
import android.view.View
import android.view.animation.Animation
import android.view.animation.AnimationUtils
import androidx.constraintlayout.widget.ConstraintLayout
import androidx.core.content.ContextCompat
import androidx.core.content.res.getColorOrThrow
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.utils.getColorFromAttr
import kotlinx.android.synthetic.main.layout_component_progress_button.view.*

class ProgressButton(ctx: Context, attr: AttributeSet? = null):
        ConstraintLayout(ctx, attr) {
    private val view: View = LayoutInflater.from(ctx)
            .inflate(R.layout.layout_component_progress_button, this, true)
    private var fadeOutAnim: Animation
    private var fadeInAnim: Animation
    private var onProgress = false
    var backgroundTint = ctx.getColorFromAttr(R.attr.colorPrimary)
    var text = ""
    set(value) {
        field = value
        view.text.text = value
    }

    var progressText = ""
    var textColor = ContextCompat.getColor(ctx, R.color.white)
    var textSize = resources.getDimension(R.dimen.progress_button_text_size)
    var cornerRadius = resources.getDimensionPixelSize(R.dimen.button_corner_radius)
    var progressColor = ContextCompat.getColor(ctx, R.color.white)
    var showTextOnProgress = false
    var disabled = false
    set(value) {
        field = value
        view.btn.isEnabled = !value
    }

    init {
        val typedArray = ctx.obtainStyledAttributes(attr, R.styleable.ProgressButton)

        try {
            backgroundTint = typedArray.getColorOrThrow(R.styleable.ProgressButton_backgroundTint)
            textColor = typedArray.getColorOrThrow(R.styleable.ProgressButton_textColor)
            progressColor = typedArray.getColorOrThrow(R.styleable.ProgressButton_progressColor)
        } catch (e: IllegalArgumentException) {}

        text = typedArray.getString(R.styleable.ProgressButton_text) ?: text
        progressText = typedArray.getString(R.styleable.ProgressButton_progressText) ?: progressText
        textSize = typedArray.getDimension(R.styleable.ProgressButton_textSize, textSize)
        cornerRadius = typedArray.getDimensionPixelSize(R.styleable.ProgressButton_cornerRadius,
                cornerRadius)
        showTextOnProgress = typedArray.getBoolean(R.styleable.ProgressButton_showTextOnProgress,
                showTextOnProgress)
        disabled = typedArray.getBoolean(R.styleable.ProgressButton_disabled, disabled)
        typedArray.recycle()

        if (!showTextOnProgress) {
            (view.text.layoutParams as MarginLayoutParams).marginStart = 0
        }

        view.btn.setBackgroundColor(backgroundTint)
        view.btn.cornerRadius = cornerRadius
        view.btn.isEnabled = !disabled
        view.text.text = text
        view.text.setTextSize(TypedValue.COMPLEX_UNIT_PX, textSize)
        view.text.setTextColor(textColor)
        view.progress_circular.setIndicatorColor(progressColor)
        view.progress_circular.visibility = View.GONE

        fadeOutAnim = AnimationUtils.loadAnimation(view.context, R.anim.fade_out)
        fadeInAnim = AnimationUtils.loadAnimation(view.context, R.anim.fade_in)
    }

    fun stopProgress() {
        if (!onProgress) {
            return
        }

        view.progress_circular.startAnimation(fadeOutAnim)
        if (showTextOnProgress) view.text.startAnimation(fadeOutAnim)

        Handler(Looper.getMainLooper()).postDelayed({
            if (showTextOnProgress) view.text.visibility = View.GONE
            view.progress_circular.visibility = View.GONE

            view.text.text = text
            view.text.visibility = View.VISIBLE
            view.text.startAnimation(fadeInAnim)
            view.btn.isEnabled = true
        }, 300)
    }

    override fun setOnClickListener(l: OnClickListener?) {
        view.btn.setOnClickListener {
            view.btn.isEnabled = false
            view.text.startAnimation(fadeOutAnim)
            onProgress = true

            Handler(Looper.getMainLooper()).postDelayed({
                view.text.visibility = View.GONE

                if (showTextOnProgress) {
                    view.text.text = progressText
                    view.text.visibility = View.VISIBLE
                    view.text.startAnimation(fadeInAnim)
                }

                view.progress_circular.visibility = View.VISIBLE
                view.progress_circular.startAnimation(fadeInAnim)
            }, 300)

            l?.onClick(it)
        }
    }
}